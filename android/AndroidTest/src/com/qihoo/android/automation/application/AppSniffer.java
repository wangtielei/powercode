package com.qihoo.android.automation.application;

import java.util.Map;
import java.util.HashMap;
import java.util.List;
import java.util.ArrayList;
import java.util.Iterator;
import android.content.Context;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageManager;
//import android.content.pm.PackageManager.NameNotFoundException;
import android.app.ActivityManager;
import android.app.ActivityManager.RunningAppProcessInfo;
import android.os.Debug.MemoryInfo;
import org.apache.log4j.Logger;

import com.qihoo.android.automation.battery.BatteryResult;

public class AppSniffer
{
	private final Logger logger = Logger.getLogger(AppSniffer.class);
	private ActivityManager activityManager;
	private PackageManager packageManager;
	private Context appContext = null;
	private List<AppInfo> appList = new ArrayList<AppInfo>();
	private Map<String, AppInfo> appNameMap = new HashMap<String, AppInfo>();
	private Map<String, Integer> runningAppMap = new HashMap<String, Integer>(); //processname to process id
	private Map<Integer, Integer> userIdCountMap = new HashMap<Integer, Integer>();
	
	
	public AppSniffer(Context appContext)
	{
		this.appContext = appContext;
		this.activityManager = (ActivityManager)(this.appContext.getSystemService("activity"));
		this.packageManager = this.appContext.getPackageManager();
	}
	
	/**
	 * �����Ѱ�װ��Ӧ�����
	 */
	public void loadInstalledApplications()
	{
		logger.debug("Enter loadInstalledApplications()");
		if (this.appContext == null)
		{
			logger.error("appContext is null, Do nothing.");
			return;
		}
		
		//clear data
		appList.clear();
		appNameMap.clear();
		userIdCountMap.clear();
				
		//load running apps
		getRunningApps();
				
		//get all install application
		List<ApplicationInfo> appInfoList = this.packageManager.getInstalledApplications(0);
		
		for (Iterator item = appInfoList.iterator(); item.hasNext();)
		{
			ApplicationInfo appInfo = (ApplicationInfo)(item.next());

			try
			{
				if (this.appNameMap.containsKey(appInfo.processName))
					continue;
				
				AppInfo appItem = new AppInfo();
				//getApplicationIcon()函数在某些手机上会失败，所以暂时注释掉
				//appItem.setAppIcon(this.packageManager.getApplicationIcon(appInfo));
				appItem.setAppLabel(this.packageManager.getApplicationLabel(appInfo).toString());
				appItem.setAppName(appInfo.processName);
				appItem.setAppPermission(appInfo.permission);
				appItem.setClassName(appInfo.className);
				appItem.setDataDir(appInfo.dataDir);
				appItem.setSourceDir(appInfo.sourceDir);
				appItem.setTargetSdkVersion(appInfo.targetSdkVersion);
				appItem.setUserID(appInfo.uid);
				
				if (userIdCountMap.containsKey(appInfo.uid))
				{
					//如果user id有重复，说明是系统应用，可以忽略
					userIdCountMap.put(appInfo.uid, 2);
					continue;
				}
				else
				{
					userIdCountMap.put(appInfo.uid, 1);
				}
				
				this.packageManager.getPackagesForUid(appInfo.uid);
				if (runningAppMap.containsKey(appInfo.processName))
				{
					appItem.setIsRunning(true);
					appItem.setPID(runningAppMap.get(appInfo.processName));
					getMemorySize(appItem);
				}
				this.appList.add(appItem);
				this.appNameMap.put(appItem.getAppName(), appItem);
			}
			catch (Exception ex)
			{
				logger.error(ex.getMessage());
			}
		}	
		
		//去除掉系统的应用，系统应用的特点是多个应用公用一个user id
		Iterator it = userIdCountMap.entrySet().iterator();
		while (it.hasNext())
		{
			Map.Entry<Integer, Integer> entry = (Map.Entry<Integer, Integer>)it.next();
			int count = entry.getValue();
			if (count == 1)
				continue;
			
			int userID = entry.getKey();
			
			for (int i=this.appList.size()-1; i>=0; --i)
			{
				AppInfo appInfo = this.appList.get(i);
				
				if (appInfo.getUserID() == userID)
				{
					this.appList.remove(i);
				}
			}
		}
		logger.debug("Leave loadInstalledApplications()");
	}	
	
	public boolean isAppRunning(String processName)
	{
		return runningAppMap.containsKey(processName);
	}
	
	/**
	 * 打印App信息
	 */
	public void printAppsInfo()
	{
		for(int i=0; i<this.appList.size(); ++i)
		{
			AppInfo appItem = this.appList.get(i);
			
			//print appItem
			logger.info(String.format("=============No.%d==========", i+1));
			logger.info("AppLabel: " + appItem.getAppLabel());
			logger.info("AppName: " + appItem.getAppName());
			logger.info("AppPermission: " + appItem.getAppPermission());
			logger.info("ClassName: " + appItem.getClassName());
			logger.info("DataDir: " + appItem.getDataDir());
			logger.info("SourceDir: + " + appItem.getSourceDir());
			logger.info(String.format("TargetSdkVersion: %d", appItem.getTargetSdkVersion()));
			logger.info(String.format("UserID: %d", appItem.getUserID()));
			logger.info(String.format("App has Icon? %s", appItem.getAppIcon()==null?"No":"Yes"));
			logger.info(String.format("App is in running: %s", appItem.getIsRunning()==true?"Yes":"No"));
			if (appItem.getIsRunning() == true)
			{
				logger.info(String.format("PID: %d", appItem.getPID()));
				logger.info(String.format("totalPrivateDirtyMemory: %d", appItem.getPrivateDirtyMemory()));
				logger.info(String.format("totalPssMemory: %d", appItem.getPssMemory()));
				logger.info(String.format("totalSharedDirtyMemory: %d", appItem.getSharedDirtyMemory()));
			}
			logger.info("");
		}
	}
	
	/**
	 * 获得运行的应用程序
	 */
	private void getRunningApps()
	{
		//clear data
		runningAppMap.clear();
		
		List<RunningAppProcessInfo> runningProcessInfoList = this.activityManager.getRunningAppProcesses();
		for (Iterator item = runningProcessInfoList.iterator(); item.hasNext();)
		{
			RunningAppProcessInfo processInfo = (RunningAppProcessInfo)(item.next());
			runningAppMap.put(processInfo.processName, processInfo.pid);
		}
	}
	
	/**
	 * 获得应用的内存大小
	 * @param appInfo
	 */
	private void getMemorySize(AppInfo appInfo)
	{
		int[] pids = new int[1];
		pids[0] = appInfo.getPID();
		MemoryInfo[] memoryInfos = this.activityManager.getProcessMemoryInfo(pids);
		
		if (memoryInfos.length == 1)
		{
			MemoryInfo memoryInfo = memoryInfos[0];
			
			appInfo.setPrivateDirtyMemory(memoryInfo.getTotalPrivateDirty());
			appInfo.setPssMemory(memoryInfo.getTotalPss());
			appInfo.setSharedDirtyMemory(memoryInfo.getTotalSharedDirty());
		}		
	}
	
	/**
	 * 获得应用数量
	 * @return
	 */
	public int getInstalledAppCount()
	{
		return this.appList.size();
	}
	
	
	/**
	 * 根据索引获得应用
	 * @param index
	 * @return
	 * @throws Exception
	 */
	public AppInfo getAppInfobyIndex(int index) throws Exception
	{
		if (index < 0 || index >= this.appList.size())
		{
			throw new Exception("index is not valid.");
		}
		
		return this.appList.get(index);
	}
	
	/**
	 * 
	 * @param uid
	 * @return
	 * @throws Exception
	 */
	public AppInfo getAppInfobyUid(String appName) throws Exception
	{
		if (!this.appNameMap.containsKey(appName))
		{
			throw new Exception("appName is not valid.");
		}
		
		return this.appNameMap.get(appName);
	}
	
	/**
	 * 判断某个APP是否存在
	 * @param appName
	 * @return
	 */
	public Boolean hasApp(String appName)
	{
		for(int i=0; i<getInstalledAppCount(); ++i)
		{
			try
			{
			AppInfo appInfo = this.getAppInfobyIndex(i);
			
			if (appInfo.getAppName().equalsIgnoreCase(appName))
			{
				return true;
			}
			}
			catch (Exception ex)
			{
				return false;
			}
		}
		
		return false;
	}
}
