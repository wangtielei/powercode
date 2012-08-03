package com.qihoo.android.automation.battery;

import org.apache.log4j.Logger;

import com.qihoo.android.automation.application.AppInfo;
import com.qihoo.android.automation.application.AppSniffer;
import android.content.Context;
import android.net.TrafficStats;
import java.io.RandomAccessFile;
import java.util.Map;
import java.util.HashMap;
import java.util.List;
import java.util.ArrayList;
import java.util.Iterator;

public class BatteryCounter
{
	private final Logger        logger = Logger.getLogger(BatteryCounter.class);    //logger
	private Context             appContext = null;                                  //context
	private AppSniffer          appSniffer = null;                                  //app sniffer
	private long                beginSysCPU;                                        //开始时的系统CPU占用
	private long                endSysCPU;                                          //结束时系统CPU占用
	private long                beginTraffic;                                       //开始时系统流量
	private long                endTraffic;                                         //结束时系统流量

	private Map<String, BatteryResult> batteryResultMap = new HashMap<String, BatteryResult>(); //原始电量has
	private List<BatteryResult> orderedResultList = new ArrayList<BatteryResult>(); //排好序的电量结果列表
	
	/**
	 * 电量计算类的构造函数
	 * @param appContext
	 */
	public BatteryCounter(Context appContext)
	{
		this.appContext = appContext;
		appSniffer = new AppSniffer(this.appContext);		
		beginSysCPU = 0;
		endSysCPU = 0;
		beginTraffic = 0;
		endTraffic = 0;
	}
	
	/**
	 * 获得当前系统CPU占用情况
	 * @param cpuInfo
	 */
	private long getSystemCPU()
	{
		try
	    {
			/*
			 * 0    1       2     3      4         5      6   7    8 9
               cpu  1253014 14284 520370 4429906   18282  345 2548 0 0
               cpu  432661  13295 86656  422145968 171474 233 5346
				               参数 解释
				1. user (432661) 从系统启动开始累计到当前时刻，用户态的CPU时间（单位：jiffies） ，不包含 nice值为负进程。1jiffies=0.01秒
				2. nice (13295) 从系统启动开始累计到当前时刻，nice值为负的进程所占用的CPU时间（单位：jiffies）
				3. system (86656) 从系统启动开始累计到当前时刻，核心时间（单位：jiffies）
				4. idle (422145968) 从系统启动开始累计到当前时刻，除硬盘IO等待时间以外其它等待时间（单位：jiffies）
				5. iowait (171474) 从系统启动开始累计到当前时刻，硬盘IO等待时间（单位：jiffies） ，
				6. irq (233) 从系统启动开始累计到当前时刻，硬中断时间（单位：jiffies）
				7. softirq (5346) 从系统启动开始累计到当前时刻，软中断时间（单位：jiffies）				
				CPU时间=user+system+nice+idle+iowait+irq+softirq
			 */
	      RandomAccessFile localRandomAccessFile = new RandomAccessFile("/proc/stat", "r");
	      String fileContent = localRandomAccessFile.readLine().trim();
	      String[] arrayOfString = fileContent.split(" ");
	      
	      //logger.error("sys stat content:" + fileContent);
	      //logger.error(String.format("user:%s, nice:%s, system:%s", arrayOfString[2], arrayOfString[3], arrayOfString[4]));
	      long cpuValue = Long.parseLong(arrayOfString[2]) + 
	    		  Long.parseLong(arrayOfString[3]) + 
	    		  Long.parseLong(arrayOfString[4]);
	      localRandomAccessFile.close();
	      return cpuValue;
	    }
	    catch (Exception localException)
	    {
	      localException.printStackTrace();
	    }
		
		return 0;
	}
	
	
	/**
	 * 获得某个应用从运行开始到现在的CPU占用情况
	 * @param processID
	 * @param cpuInfo
	 */
	private long getAppCPU(int processID, long defaultValue)
	{
		try
	    {
		  RandomAccessFile localRandomAccessFile = new RandomAccessFile("/proc/" + processID + "/stat", "r");
		  String fileContent = localRandomAccessFile.readLine().trim();
		  String[] arrayOfString = fileContent.split("\\s+");
		  localRandomAccessFile.close();
	      
		  /**
		  file /proc/544/stat content:
		   * 544 (om.trafficstats) S 94 94 
		   * 0 0 -1 4194624 4034 
		   * 0 2 0 123 25 
		   * 0 0 20 0 6 
		   * 0 5229 272642048 5452 4294967295 
		   * 32768 36524 0 0 0 
		   * 0 4612 0 38120 4294967295 
		   * 0 0 17 0 0 
		   * 3 0 0 0
		   * 
		   * fields explain:
		   *13. utime %lu
				Amount of time that this process has been scheduled in user mode, measured in clock ticks (divide by sysconf(_SC_CLK_TCK). This includes guest time, guest_time (time spent running a virtual CPU, see below), so that applications that are not aware of the guest time field do not lose that time from their calculations.
				
			14. stime %lu
			Amount of time that this process has been scheduled in kernel mode, measured in clock ticks (divide by sysconf(_SC_CLK_TCK).
			
			15. cutime %ld
			Amount of time that this process's waited-for children have been scheduled in user mode, measured in clock ticks (divide by sysconf(_SC_CLK_TCK). (See also times(2).) This includes guest time, cguest_time (time spent running a virtual CPU, see below).
			
			16. cstime %ld
			Amount of time that this process's waited-for children have been scheduled in kernel mode, measured in clock ticks (divide by sysconf(_SC_CLK_TCK). 
		   */
		  //logger.error("pro stat content:" + fileContent);
	      //logger.error(String.format("13:%s, 14:%s, 15:%s, 16:%s", arrayOfString[13], arrayOfString[14],
	    //		  arrayOfString[15],arrayOfString[16]));
		  long cpuValue = Long.parseLong(arrayOfString[13]) + 
				  		  Long.parseLong(arrayOfString[14]) +
				  		  Long.parseLong(arrayOfString[15]) + 
				  		  Long.parseLong(arrayOfString[16]);
		  return cpuValue;
	    }
	    catch (Exception ex)
	    {
	      ex.printStackTrace();
	    }
		
		return defaultValue; 
	}
	
	/**
	 * 重置电量消耗记录
	 */
	public void resetBatteryStat()
	{
		batteryResultMap.clear();
		this.endTraffic = 0;		
		this.beginTraffic = TrafficStats.getTotalRxBytes() + TrafficStats.getTotalTxBytes();
		beginSysCPU = getSystemCPU();		
		
		//logger.error(String.format("beginTraffic:%d, beginCPU:%d", this.beginTraffic, this.beginSysCPU));
		
		appSniffer.loadInstalledApplications();
		
		for (int i=0; i<appSniffer.getInstalledAppCount(); ++i)
		{
			try
			{
				AppInfo appInfo = appSniffer.getAppInfobyIndex(i);						
				long send = TrafficStats.getUidTxBytes(appInfo.getUserID());
				long receive = TrafficStats.getUidRxBytes(appInfo.getUserID());
				
				if (send + receive <= 0)
					continue;
				
				BatteryResult batteryResult = new BatteryResult();
				batteryResult.appInfo = appInfo;
				batteryResult.beginBytes = send+receive;
				batteryResult.timeStamp = System.currentTimeMillis();
				
				//Lv Jun update start 20120412
				if(batteryResult.memList == null){
					ArrayList<Integer> memList = new ArrayList<Integer>();
					batteryResult.memList = memList;
					//由于有时需要刚启动某APK看到其启动画面时就调用计算方法，而此时可能memList还没有被添加元素值，因此有可能出现下标越界异常，所以添加-1避免出现这种异常。
					batteryResult.memList.add(-1);
				}
				//Lv Jun update end
				
				//如果进程运行着则获得CPU占用
				if(batteryResult.appInfo.getIsRunning())
				{
					batteryResult.beginCPU = getAppCPU(batteryResult.appInfo.getPID(), 0);
					batteryResult.memMax = appInfo.getPrivateDirtyMemory();
					batteryResult.memSum = batteryResult.memMax;
					batteryResult.memCount = 1;
					//logger.error(String.format("appname:%s, uid:%d, pid:%d, running", 
					//		appInfo.getAppName(), appInfo.getUserID(), appInfo.getPID()));
				}
				else
				{
					//logger.error(String.format("appname:%s, uid:%d, not run", 
					//		appInfo.getAppName(), 
					//		appInfo.getUserID()));
				}
				
				batteryResultMap.put(appInfo.getAppName(), batteryResult);
			}
			catch (Exception ex)
			{
				logger.error("resetBatteryStat get exception RESET : " + ex.getMessage());
			}
		}	
	}
	
	/**
	 * 统计每个应用当前的CPU消耗和流量占用。
	 */
	public void scanAppBattery()
	{
		logger.info("enter scanAppBattery()\n");
		appSniffer.loadInstalledApplications();
		
		for (int i=0; i<appSniffer.getInstalledAppCount(); ++i)
		{
			try
			{
				AppInfo appInfo = appSniffer.getAppInfobyIndex(i);	
				
				long send = TrafficStats.getUidTxBytes(appInfo.getUserID());
				long receive = TrafficStats.getUidRxBytes(appInfo.getUserID());
				
				if (send + receive <= 0)
					continue;
							
				//if (!appInfo.getAppName().equalsIgnoreCase("com.qihoo.androidbrowser"))
				//if (!appInfo.getAppName().equalsIgnoreCase("com.qingzhi.weibocall"))
				//	continue;
					
				BatteryResult batteryResult = null;
				
				if (!batteryResultMap.containsKey(appInfo.getAppName()))
				{
					batteryResult = new BatteryResult();
					batteryResult.appInfo = appInfo;
					batteryResult.beginBytes = 0;		
					
					//如果进程运行着则获得CPU占用
					if(batteryResult.appInfo.getIsRunning())
					{	
						batteryResult.endCPU = getAppCPU(batteryResult.appInfo.getPID(), 0);
						//calc memory
						batteryResult.memMax = appInfo.getPrivateDirtyMemory();
						batteryResult.memSum = batteryResult.memMax;
						batteryResult.memCount = 1;	
						//Lv Jun update start 20120412
						if(batteryResult.memList == null){
							ArrayList<Integer> memList = new ArrayList<Integer>();
							batteryResult.memList=memList;
						}
						batteryResult.memList.add(appInfo.getPrivateDirtyMemory());
						//Lv Jun update end
					}
					
					//如果是新运行的进程则需要赋值初始的CPU资源
					batteryResultMap.put(appInfo.getAppName(), batteryResult);
				}
				else
				{
					batteryResult = batteryResultMap.get(appInfo.getAppName());	
					
					//前一周期没运行，本周起运行了
					if (!batteryResult.appInfo.getIsRunning() && appInfo.getIsRunning())
					{
						batteryResult.endCPU = getAppCPU(appInfo.getPID(), 0);
					}
					//前一周期运行了，本周起没运行
					if (batteryResult.appInfo.getIsRunning() && !appInfo.getIsRunning())
					{
						batteryResult.cpuDiffAccu += (batteryResult.endCPU - batteryResult.beginCPU);
						batteryResult.beginCPU = 0;
						batteryResult.endCPU = 0;
					}
					//前一周期运行，本周起还是运行
					else if (batteryResult.appInfo.getIsRunning() && appInfo.getIsRunning())
					{
						batteryResult.endCPU = getAppCPU(batteryResult.appInfo.getPID(), batteryResult.endCPU);						
					}
					//前一周期没运行，本周起也没运行
					//else
					//{
						//什么也不用干
					//}
					
					//calc memory
					if (appInfo.getIsRunning())
					{
						batteryResult.memSum += appInfo.getPrivateDirtyMemory();
						if (appInfo.getPrivateDirtyMemory() > batteryResult.memMax)
						{
							batteryResult.memMax = appInfo.getPrivateDirtyMemory();
						}
						++batteryResult.memCount;
						//Lv Jun update start 20120412
						if(batteryResult.memList == null){
							ArrayList<Integer> memList = new ArrayList<Integer>();
							batteryResult.memList=memList;
						}
						batteryResult.memList.add(appInfo.getPrivateDirtyMemory());
						//Lv Jun update end
					}					
					
					batteryResult.appInfo = appInfo;
				}
				batteryResult.endBytes = send+receive;
				batteryResult.timeStamp = System.currentTimeMillis();
				/*
				logger.error(String.format("%s, %d, beginT:%d, endT:%d, cpuAccu:%d, begincpu:%d, endcpu:%d", 
						batteryResult.appInfo.getAppName(), batteryResult.appInfo.getUserID(),
						batteryResult.beginBytes,
						batteryResult.endBytes,
						batteryResult.cpuDiffAccu, 
						batteryResult.beginCPU, 
						batteryResult.endCPU));*/
			}
			catch (Exception ex)
			{
				ex.printStackTrace();
				logger.error("resetBatteryStat get exception SCAN : " + ex.getMessage());
			}
		}
		logger.info("leave scanAppBattery()\n");
	}
	
	/**
	 * 计算电量
	 */
	public void calcBatteryStat(String caseName,String appName)
	{
		scanAppBattery();
		endSysCPU = getSystemCPU();			
		this.endTraffic = TrafficStats.getTotalRxBytes() + TrafficStats.getTotalTxBytes();
		long trafficDiff = this.endTraffic - this.beginTraffic;
		
		Iterator it = batteryResultMap.entrySet().iterator();
		while (it.hasNext())
		{
			Map.Entry<String, BatteryResult> entry = (Map.Entry<String, BatteryResult>)it.next();
			BatteryResult batteryResult = entry.getValue();
					
			trafficDiff += batteryResult.endBytes - batteryResult.beginBytes;
		}
		
		long cpuDiff = endSysCPU - beginSysCPU;
		
		logger.info(String.format("beginTraffic:%d, endTraffic:%d, beginCPU:%d, endCPU:%d", 
				this.beginTraffic, this.endTraffic, this.beginSysCPU, this.endSysCPU));
		//logger.error(String.format("beginTraffic:%d, endTraffic:%d, beginCPU:%d, endCPU:%d", 
		//		this.beginTraffic, this.endTraffic, this.beginSysCPU, this.endSysCPU));
		
		logger.error(String.format("System: apptrafficsum:%d, SysTrafficDiff:%d, SysCpuDiff:%d", 
				trafficDiff, 
				this.endTraffic - this.beginTraffic, 
				cpuDiff));
		
		it = batteryResultMap.entrySet().iterator();
		while (it.hasNext())
		{
			Map.Entry<String, BatteryResult> entry = (Map.Entry<String, BatteryResult>)it.next();
			BatteryResult batteryResult = entry.getValue();
			
			if (batteryResult.memCount > 0)
			{
				batteryResult.memSum = batteryResult.memSum/batteryResult.memCount;
			}
			
			long appTrafficDiff = batteryResult.endBytes - batteryResult.beginBytes;
			long appCPUDiff = batteryResult.cpuDiffAccu + (batteryResult.endCPU - batteryResult.beginCPU);
			
			batteryResult.batteryRate = 0.0f;
			batteryResult.cpuRate = 0.0f;
			batteryResult.trafficRate = 0.0f;
			
			if (trafficDiff > 0)
			{
				batteryResult.trafficRate = 1.0f*appTrafficDiff/trafficDiff*100;
			}
			
			if (cpuDiff > 0)
			{
				batteryResult.cpuRate = 1.0f*appCPUDiff/cpuDiff*100;
			}
		
			if (appTrafficDiff > trafficDiff || appTrafficDiff < 0)//invalid traffic
			{
				batteryResult.trafficRate = 0;
				logger.error(String.format("Traffic error [%d]%s, %s, beginT:%d, endT:%d, trafficdiff:%d", 
					batteryResult.appInfo.getPID(),
					batteryResult.appInfo.getAppName(), 
					batteryResult.appInfo.getAppLabel(),
					batteryResult.beginBytes,
					batteryResult.endBytes,
					appTrafficDiff));
			}
			
			
			if (appCPUDiff > cpuDiff || appCPUDiff < 0) //invalid cpu
			{
				batteryResult.cpuRate = 0;		
				logger.info(String.format("cpu [%d]%s, %s, beginCpu:%d, endCPU:%d, cpudiff:%d", 
						batteryResult.appInfo.getPID(),
						batteryResult.appInfo.getAppName(), 
						batteryResult.appInfo.getAppLabel(),						
						batteryResult.beginCPU, 
						batteryResult.endCPU, 
						appCPUDiff));
			}
			
			batteryResult.batteryRate = (batteryResult.trafficRate + batteryResult.cpuRate)/2.0f;
		}
		
		orderBatteryResult();
		printOrderedResult(caseName,appName);
	}
	
	/**
	 * 仅仅打印最近的计算结果
	 * @param caseName
	 * @param appName
	 */
	public void getBatteryStat(String caseName,String appName)
	{
		printOrderedResult(caseName,appName);
	}
	
	private void orderBatteryResult()
	{
		orderedResultList.clear();
		Iterator it = batteryResultMap.entrySet().iterator();
		while (it.hasNext())
		{
			Map.Entry<String, BatteryResult> entry = (Map.Entry<String, BatteryResult>)it.next();
			BatteryResult batteryResult = entry.getValue();
			
			int pos = -1;
			for (int k =0; k<orderedResultList.size(); ++k)
			{
				BatteryResult orderedResult = orderedResultList.get(k);
				
				if (orderedResult.batteryRate > batteryResult.batteryRate)
				{
					pos = k;
					break;
				}
			}
			
			if (pos == -1)
			{
				orderedResultList.add(batteryResult);
			}
			else
			{
				orderedResultList.add(pos, batteryResult);
			}
		}
	}
			
	public void printOrderedResult(String caseName,String appName)
	{
		/*
		logger.error("==========begin batterystat==========");
		logger.error("index | appname | uid | timestamp | rate | trafficrate | cpurate |");
		int k=0;
		for (int i=orderedResultList.size()-1; i>=0; --i)
		{
			BatteryResult batteryResult = orderedResultList.get(i);
			logger.error(String.format("%d|%s|%d|%d|%f|", ++k, batteryResult.appInfo.getAppLabel(), 
					batteryResult.appInfo.getUserID(), batteryResult.timeStamp, batteryResult.batteryRate));
		}
		logger.error("==========end batterystat==========");
		*/
		
		int k=0;
		for (int i=orderedResultList.size()-1; i>=0; --i)
		{
			BatteryResult batteryResult = orderedResultList.get(i);
			long trafficDiff = batteryResult.endBytes - batteryResult.beginBytes;
			long cpuDiff = batteryResult.cpuDiffAccu + (batteryResult.endCPU - batteryResult.beginCPU);
		
			if(appName.length()>0)
            {
	            if(appName.equals(batteryResult.appInfo.getAppName()))
                {	
            		logger.error(String.format("|battery|special|%s|%s|%s|%d|%d|%d|%d|%f|%f|%f|%f|%d|%d|",
	            			appName,
	            			caseName, 
	            			batteryResult.appInfo.getAppLabel(), 
	            			batteryResult.appInfo.getUserID(), 
	            			batteryResult.timeStamp, 
	            			trafficDiff,
	            			cpuDiff,	            			
	            			batteryResult.trafficRate,
	            			batteryResult.cpuRate,
	            			batteryResult.batteryRate,
	            			batteryResult.memSum,
	            			batteryResult.memMax,
	            			//Lv Jun update start 20120412
	            			batteryResult.memList.get(batteryResult.memList.size()-1)
	    					//Lv Jun update end
	            			));
	            	return;
	             }
            }
			else
			{
			    logger.error(String.format("|battery|%d|%s|%d|%d|%d|%d|%f|%f|%f|%f|%d|%d", 
                        ++k,
                        batteryResult.appInfo.getAppLabel(), 
                        batteryResult.appInfo.getUserID(), 
                        batteryResult.timeStamp, 
                        trafficDiff,
            			cpuDiff,                        
                        batteryResult.trafficRate,
            			batteryResult.cpuRate,
            			batteryResult.batteryRate,
            			batteryResult.memSum,
            			batteryResult.memMax,
            			//Lv Jun update start 20120412
            			batteryResult.memList.get(batteryResult.memList.size()-1)
    					//Lv Jun update end
			    		));
			}
		}
		//logger.error("==========end batterystat==========");
	}
}
