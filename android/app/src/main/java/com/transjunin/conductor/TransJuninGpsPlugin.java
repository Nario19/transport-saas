package com.transjunin.conductor;

import android.content.Context;
import android.content.Intent;
import android.os.Build;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

@CapacitorPlugin(name = "TransJuninGps")
public class TransJuninGpsPlugin extends Plugin {

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
}
