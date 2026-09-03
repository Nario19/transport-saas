package com.transjunin.conductor;

import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.pm.ServiceInfo;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Build;
import android.os.Bundle;
import android.os.IBinder;
import android.os.PowerManager;
import androidx.core.app.NotificationCompat;

import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class GpsForegroundService extends Service implements LocationListener {

    private static final String CHANNEL_ID = "transjunin_gps_channel";
    private static final int NOTIFICATION_ID = 99991;
    
    private LocationManager locationManager;
    private PowerManager.WakeLock wakeLock;
    private String vehiculoPlaca = "TRANSJUNIN";
    private long lastSendTime = 0;
    private Location lastLocation = null;

    @Override
    public void onCreate() {
        super.onCreate();

        createNotificationChannel();

        try {
            PowerManager powerManager = (PowerManager) getSystemService(Context.POWER_SERVICE);
            if (powerManager != null) {
                wakeLock = powerManager.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "TransJunin::ForegroundGpsLock");
                wakeLock.acquire();
            }
        } catch (Exception ignored) {}

        locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        if (intent != null) {
            String action = intent.getAction();
            if ("STOP".equals(action)) {
                stopSelf();
                return START_NOT_STICKY;
            }

            String placa = intent.getStringExtra("placa");
            if (placa != null && !placa.trim().isEmpty()) {
                this.vehiculoPlaca = placa.trim();
            }
        }

        Notification notification = buildNotification("Transmitiendo ruta en vivo...");
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            startForeground(NOTIFICATION_ID, notification, ServiceInfo.FOREGROUND_SERVICE_TYPE_LOCATION);
        } else {
            startForeground(NOTIFICATION_ID, notification);
        }

        startGpsListening();

        return START_STICKY;
    }

    private void startGpsListening() {
        if (locationManager == null) return;

        try {
            if (locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER)) {
                locationManager.requestLocationUpdates(
                    LocationManager.GPS_PROVIDER,
                    10000L, // 10 segundos
                    15f,    // 15 metros
                    this
                );
            }
        } catch (SecurityException ignored) {}

        try {
            if (locationManager.isProviderEnabled(LocationManager.NETWORK_PROVIDER)) {
                locationManager.requestLocationUpdates(
                    LocationManager.NETWORK_PROVIDER,
                    10000L,
                    15f,
                    this
                );
            }
        } catch (SecurityException ignored) {}
    }

    @Override
    public void onLocationChanged(Location location) {
        if (location == null) return;

        long now = System.currentTimeMillis();

        if (lastLocation != null) {
            float dist = lastLocation.distanceTo(location);
            if (dist < 10 && (now - lastSendTime) < 20000) {
                return;
            }
        }

        lastLocation = location;
        lastSendTime = now;

        final double lat = location.getLatitude();
        final double lon = location.getLongitude();
        final float speed = location.hasSpeed() ? (location.getSpeed() * 3.6f) : 0f;
        final float bearing = location.hasBearing() ? location.getBearing() : 0f;
        final double alt = location.hasAltitude() ? location.getAltitude() : 0.0;

        // Enviar por HTTP de forma 100% nativa en hilo de fondo independiente
        new Thread(() -> {
            HttpURLConnection conn = null;
            try {
                String urlStr = "https://www.transjunin.com/api/gps/traccar"
                        + "?id=" + URLEncoder.encode(vehiculoPlaca, "UTF-8")
                        + "&lat=" + lat
                        + "&lon=" + lon
                        + "&speed=" + speed
                        + "&bearing=" + bearing
                        + "&altitude=" + alt;

                URL url = new URL(urlStr);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(8000);
                conn.connect();

                int code = conn.getResponseCode();
                if (code == 200) {
                    InputStream in = conn.getInputStream();
                    byte[] buffer = new byte[128];
                    while (in.read(buffer) != -1) {}
                    in.close();
                }
            } catch (Exception ignored) {
            } finally {
                if (conn != null) {
                    conn.disconnect();
                }
            }
        }).start();
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                CHANNEL_ID,
                "TransJunín GPS En Vivo",
                NotificationManager.IMPORTANCE_LOW
            );
            channel.setDescription("Servicio de telemetría satelital continua en segundo plano");
            NotificationManager manager = getSystemService(NotificationManager.class);
            if (manager != null) {
                manager.createNotificationChannel(channel);
            }
        }
    }

    private Notification buildNotification(String text) {
        Intent notificationIntent = new Intent(this, MainActivity.class);
        PendingIntent pendingIntent = PendingIntent.getActivity(
            this,
            0,
            notificationIntent,
            PendingIntent.FLAG_IMMUTABLE | PendingIntent.FLAG_UPDATE_CURRENT
        );

        return new NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle("TransJunín Conductor")
            .setContentText(text)
            .setSmallIcon(R.mipmap.ic_launcher)
            .setContentIntent(pendingIntent)
            .setOngoing(true)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .build();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        if (locationManager != null) {
            locationManager.removeUpdates(this);
        }
        if (wakeLock != null && wakeLock.isHeld()) {
            wakeLock.release();
        }
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    @Override public void onStatusChanged(String provider, int status, Bundle extras) {}
    @Override public void onProviderEnabled(String provider) {}
    @Override public void onProviderDisabled(String provider) {}
}
