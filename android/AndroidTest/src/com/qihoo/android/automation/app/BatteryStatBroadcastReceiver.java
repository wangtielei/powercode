package com.qihoo.android.automation.app;

import java.util.HashMap;
import java.util.Map;
import java.util.Timer;

import org.apache.log4j.Logger;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;

import com.qihoo.android.automation.battery.BatteryCounter;

public class BatteryStatBroadcastReceiver extends BroadcastReceiver
{
	private final Logger logger = Logger.getLogger(BatteryStatBroadcastReceiver.class);
	private static final String BATTERY_CMD = "battery";
	private static final String BATTERY_CALC = "calc";
	private static final String BATTERY_GET = "get";
	private static final String BATTERY_RESET = "reset";
	
	private static final String BATTERY_CASENAME = "casename";
	private static final String BATTERY_APPNAME = "appname";
	
	
	private Context appContext = null;
	private BatteryCounter batteryCounter = null;
	private Timer batteryTimer = null;
	private BatteryTimer scheduler;
	
	public BatteryStatBroadcastReceiver(Context appContext)
	{
		this.appContext = appContext;
		batteryCounter = new BatteryCounter(this.appContext);		
	}
	
	@Override
    public void onReceive(Context context, Intent intent)
    {   
		String caseName="";
    	String appName="";
		Bundle b=intent.getExtras();  
        if (b != null)
        {
        	Map<String, String> pairMap = new HashMap<String, String>();
            Object[] lstName=b.keySet().toArray();
            for(int i=0;i<lstName.length;i++)  
            {
                String keyName=lstName[i].toString(); 
                String value = String.valueOf(b.get(keyName));
              //  logger.info(String.format("%s = %s", keyName, value));  
                pairMap.put(keyName, value);
            }
            if(pairMap.containsKey(BATTERY_CASENAME))
            {
            	caseName=pairMap.get(BATTERY_CASENAME);
            }
            if(pairMap.containsKey(BATTERY_APPNAME))
            {
            	appName=pairMap.get(BATTERY_APPNAME);
            }
    		
            if (pairMap.containsKey(BATTERY_CMD))
            {        
            	
            	if (pairMap.get(BATTERY_CMD).equalsIgnoreCase(BATTERY_CALC))
            	{
            		calcAppBatteryStat( caseName, appName);
            	}
            	else if (pairMap.get(BATTERY_CMD).equalsIgnoreCase(BATTERY_RESET))
            	{
            		resetAppBatteryStat();
            	}
            	else if (pairMap.get(BATTERY_CMD).equalsIgnoreCase(BATTERY_GET))
            	{
            		getAppBatteryStat(caseName, appName);
            	}
            }
        }
    }
	
	public void calcAppBatteryStat(String caseName,String appName)
	{
		logger.debug("Enter calcAppBatteryStat()");	
		
		if (batteryTimer != null)
			batteryTimer.cancel();
		
		batteryTimer = null;
		
		batteryCounter.calcBatteryStat(caseName,appName);		
		logger.debug("Leave calcAppBatteryStat()");
	}
	
	public void getAppBatteryStat(String caseName,String appName)
	{
		logger.debug("Enter getAppBatteryStat()");					
		batteryCounter.getBatteryStat(caseName,appName);		
		logger.debug("Leave getAppBatteryStat()");
	}
	
	public void resetAppBatteryStat()
	{
		logger.debug("Enter resetAppBatteryStat()");
		batteryCounter.resetBatteryStat();	
		
		if (batteryTimer != null)
			batteryTimer.cancel();
		
		batteryTimer = new Timer();
		scheduler = new BatteryTimer(batteryCounter);
		batteryTimer.schedule(scheduler, 1000, 1000);
		logger.debug("Leave resetAppBatteryStat()");
	}	
}
