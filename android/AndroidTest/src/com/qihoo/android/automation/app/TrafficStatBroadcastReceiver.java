package com.qihoo.android.automation.app;

import java.util.HashMap;
import java.util.Map;

import org.apache.log4j.Logger;

import com.qihoo.android.automation.trafficstat.TrafficCounter;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;

public class TrafficStatBroadcastReceiver extends BroadcastReceiver
{
	private final Logger logger = Logger.getLogger(TrafficStatBroadcastReceiver.class);
	private static final String TRAFFIC_CMD = "traffic";
	private static final String TRAFFIC_CALC = "calc";
	private static final String TRAFFIC_GET = "get";
	private static final String TRAFFIC_RESET = "reset";
	private static final String TRAFFIC_ORDER = "order";
	
	
	private static final String BATTERY_CASENAME = "casename";
	private static final String BATTERY_APPNAME = "appname";
	
	private Context appContext = null;
	TrafficCounter trafficCounter = null;
	
	
	public TrafficStatBroadcastReceiver(Context appContext)
	{
		this.appContext = appContext;
		trafficCounter = new TrafficCounter(this.appContext);
	}
	
	@Override
    public void onReceive(Context context, Intent intent)
    {   
        Bundle b=intent.getExtras();  
        if (b != null)
        {
        	String caseName="";
        	String appName="";
        	Map<String, String> pairMap = new HashMap<String, String>();
            Object[] lstName=b.keySet().toArray();
            for(int i=0;i<lstName.length;i++)  
            {
                String keyName=lstName[i].toString(); 
                String value = String.valueOf(b.get(keyName));
                logger.debug(String.format("%s = %s", keyName, value));  
                pairMap.put(keyName, value);
            }
            
            //获得case名称
            if(pairMap.containsKey(BATTERY_CASENAME))
            {
            	caseName=pairMap.get(BATTERY_CASENAME);
            }
            
            //获得应用名称
            if(pairMap.containsKey(BATTERY_APPNAME))
            {
            	appName=pairMap.get(BATTERY_APPNAME);
            }
            
            if (pairMap.containsKey(TRAFFIC_CMD))
            {            
            	String orderMode = TrafficCounter.ORDER_BY_TOTAL;
        		
        		if (pairMap.containsKey(TRAFFIC_ORDER))
        		{
        			if (pairMap.get(TRAFFIC_ORDER).equalsIgnoreCase("send"))
        			{
        				orderMode = TrafficCounter.ORDER_BY_SEND;
        			}
        			else if (pairMap.get(TRAFFIC_ORDER).equalsIgnoreCase("receive"))
        			{
        				orderMode = TrafficCounter.ORDER_BY_RECEIVE;
        			}
        			else if (pairMap.get(TRAFFIC_ORDER).equalsIgnoreCase("lastsend"))
        			{
        				orderMode = TrafficCounter.ORDER_BY_LAST_RECEIVE;
        			}
        			else if (pairMap.get(TRAFFIC_ORDER).equalsIgnoreCase("lastreceive"))
        			{
        				orderMode = TrafficCounter.ORDER_BY_LAST_SEND;
        			}
        			else if (pairMap.get(TRAFFIC_ORDER).equalsIgnoreCase("last"))
        			{
        				orderMode = TrafficCounter.ORDER_BY_LAST_LAST;
        			}
        		}
        		
            	if (pairMap.get(TRAFFIC_CMD).equalsIgnoreCase(TRAFFIC_GET))
            	{            		
            		getAppTrafficStat(orderMode,caseName,appName);
            	}
            	else if (pairMap.get(TRAFFIC_CMD).equalsIgnoreCase(TRAFFIC_CALC))
            	{
            		calcAppTrafficStat(orderMode,caseName,appName);
            	}
            	else if (pairMap.get(TRAFFIC_CMD).equalsIgnoreCase(TRAFFIC_RESET))
            	{
            		resetAppTrafficStat();
            	}
            }
        }
    }    
	
	public void calcAppTrafficStat(String orderMode,String caseName,String appName)
	{
		logger.info("Enter calcAppTrafficStat()");		
		trafficCounter.calcTrafficStat(orderMode,caseName,appName);        
		logger.info("Leave calcAppTrafficStat()");
	}
	
	public void getAppTrafficStat(String orderMode,String caseName,String appName)
	{
		logger.info("Enter getAppTrafficStat()");		
		trafficCounter.getTrafficStat(orderMode,caseName,appName);        
		logger.info("Leave getAppTrafficStat()");
	}
	
	public void resetAppTrafficStat()
	{
		logger.info("Enter resetAppTrafficStat()");
		trafficCounter.resetTrafficStat();		
		logger.info("Leave resetAppTrafficStat()");
	}
}
