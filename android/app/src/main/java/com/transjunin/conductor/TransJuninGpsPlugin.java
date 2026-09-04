package com.transjunin.conductor;

import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.os.Build;
import android.provider.Settings;
import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

@CapacitorPlugin(name = "TransJuninGps")
public class TransJuninGpsPlugin extends Plugin {

    public static boolean isLocationMock(Location location) {
        if (location == null) return false;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            return location.isMock();
        } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN_MR2) {
            return location.isFromMockProvider();
        }
        return false;
    }

    @PluginMethod
    public void startTracking(PluginCall call) {
        String placa = call.getString("placa", "TRANSJUNIN");
        Context context = getContext();

        Intent serviceIntent = new Intent(context, GpsForegroundService.class);
        serviceIntent.putExtra("placa", placa);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            context.startForegroundService(serviceIntent);
        } else {
            context.startService(serviceIntent);
        }

        call.resolve();
    }

    @PluginMethod
    public void stopTracking(PluginCall call) {
        Context context = getContext();
        Intent serviceIntent = new Intent(context, GpsForegroundService.class);
        serviceIntent.setAction("STOP");
        context.stopService(serviceIntent);

        call.resolve();
    }

    @PluginMethod
    public void checkMockLocation(PluginCall call) {
        Context context = getContext();
        JSObject ret = new JSObject();
        boolean isMock = false;
        String reason = "";

        try {
            LocationManager locationManager = (LocationManager) context.getSystemService(Context.LOCATION_SERVICE);
            if (locationManager != null) {
                Location gpsLoc = null;
                try {
                    gpsLoc = locationManager.getLastKnownLocation(LocationManager.GPS_PROVIDER);
                } catch (SecurityException ignored) {}

                Location netLoc = null;
                try {
                    netLoc = locationManager.getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
                } catch (SecurityException ignored) {}

                if (gpsLoc != null && isLocationMock(gpsLoc)) {
                    isMock = true;
                    reason = "GPS_PROVIDER_MOCK";
                } else if (netLoc != null && isLocationMock(netLoc)) {
                    isMock = true;
                    reason = "NETWORK_PROVIDER_MOCK";
                }

                if (!isMock && Build.VERSION.SDK_INT < Build.VERSION_CODES.M) {
                    try {
                        if (Settings.Secure.getInt(context.getContentResolver(), Settings.Secure.ALLOW_MOCK_LOCATION, 0) != 0) {
                            isMock = true;
                            reason = "ALLOW_MOCK_LOCATION_ENABLED";
                        }
                    } catch (Exception ignored) {}
                }

                Location bestLoc = gpsLoc != null ? gpsLoc : netLoc;
                if (bestLoc != null) {
                    ret.put("latitude", bestLoc.getLatitude());
                    ret.put("longitude", bestLoc.getLongitude());
                    ret.put("accuracy", bestLoc.getAccuracy());
                    ret.put("hasLocation", true);
                } else {
                    ret.put("hasLocation", false);
                }
            }
        } catch (Exception e) {
            reason = "EXCEPTION: " + e.getMessage();
        }

        ret.put("isMock", isMock);
        ret.put("reason", reason);
        call.resolve(ret);
    }
}
