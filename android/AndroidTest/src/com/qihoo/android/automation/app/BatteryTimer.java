package com.qihoo.android.automation.app;

import java.util.TimerTask;
import org.apache.log4j.Logger;
import com.qihoo.android.automation.battery.BatteryCounter;

public class BatteryTimer extends TimerTask 
{
	private BatteryCounter batteryCounter = null;
	private final Logger logger = Logger.getLogger(BatteryTimer.class);
	
	public BatteryTimer(BatteryCounter counter)
	{
		batteryCounter = counter;
	}
	
	@Override
	public void run() 
	{
		logger.debug("enter BatteryTimer::run()");
		batteryCounter.scanAppBattery();
		logger.debug("leave BatteryTimer::run()");
	}
}
