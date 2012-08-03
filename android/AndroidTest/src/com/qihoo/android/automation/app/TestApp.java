package com.qihoo.android.automation.app;

import org.apache.log4j.Level;
import org.apache.log4j.Logger;
import com.qihoo.android.automation.androidtest.R;
import android.app.ListActivity;
import com.qihoo.android.automation.utility.*;
import android.content.IntentFilter;
import android.os.Bundle;

public class TestApp extends ListActivity 
{
	static final String INTENAL_ACTION_TRAFFIC = "com.qihoo.android.test.gettrafficstat";  
	static final String INTENAL_ACTION_BATTERY = "com.qihoo.android.test.getbatterystat";
	
	//logger instance
	private Logger logger = null;
	
	private TrafficStatBroadcastReceiver trafficStatReceiver = null;
	private BatteryStatBroadcastReceiver batteryStatReceiver = null;
	
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) 
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main2);
        
        //init logger configuration
        //Log4jConfigurator.initLogWithLevel("wxtest.log", Level.ERROR);
 
        Log4jConfigurator.initLogWithLevel("wxtest.log", Level.ERROR);   
        logger = Logger.getLogger(TestApp.class);
       logger.debug("Let's begin");
      
        //register traffic stat receiver
       
        trafficStatReceiver = new TrafficStatBroadcastReceiver(this);        
        IntentFilter filter = new IntentFilter();
        filter.addAction(INTENAL_ACTION_TRAFFIC);       
        registerReceiver(trafficStatReceiver, filter);
        
        batteryStatReceiver = new BatteryStatBroadcastReceiver(this);
        filter = new IntentFilter();
        filter.addAction(INTENAL_ACTION_BATTERY);       
        registerReceiver(batteryStatReceiver, filter);     
    }
    
    @Override
    public void onDestroy()
    {
        super.onDestroy();
        unregisterReceiver(this.trafficStatReceiver);
        unregisterReceiver(this.batteryStatReceiver);
    }
}