package com.qihoo.android.automation.battery;

import java.util.ArrayList;

import com.qihoo.android.automation.application.AppInfo;

public class BatteryResult
{
	public AppInfo appInfo;
	
	//计算时间戳
	public long timeStamp = 0;
	
	//CPU占用记录
	public long beginCPU=0;
	public long endCPU=0;
	public long cpuDiffAccu=0;
	
	//流量占用记录
	public long beginBytes = 0;
	public long endBytes = 0;
	
	//近似电量消耗
	public float batteryRate = 0.0f;	
	public float trafficRate = 0.0f;
	public float cpuRate = 0.0f;
	
	//内存总量
	public float memSum = 0.0f;
	public int  memMax = 0;
		//Lv Jun update start 20120412
	public ArrayList<Integer> memList;
		//Lv Jun update end
	//内存累计次数
	public int  memCount=0;
}
