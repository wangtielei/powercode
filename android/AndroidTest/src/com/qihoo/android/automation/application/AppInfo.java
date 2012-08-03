package com.qihoo.android.automation.application;

import android.graphics.drawable.Drawable;


public class AppInfo
{
	private String appLabel;           //application label
	private Drawable appIcon = null;   //application icon
	private String appName;            //process name
	private String appPermission;      //application permission
	private String className;          //class name
	private String dataDir;            //data directory
	private String sourceDir;          //source directory
	private int targetSdkVersion;      //target sdk version
	private int userID;                //user id
	
	//if it's running, these fields are valid	
	private boolean isRunning;         //whether it's running
	private int pid;                   //process id
	private int totalPrivateDirtyMemory; //total private dirty memory usage in kB
	private int totalPssMemory;        //total PSS memory usage in kB.
	private int totalSharedDirtyMemory;//total shared dirty memory usage in kB.
	
	public AppInfo()
	{
		this.appLabel = "";
		this.appIcon = null;
		this.appName = "";
		this.appPermission = "";
		this.className = "";
		this.dataDir = "";
		this.sourceDir = "";
		this.targetSdkVersion = 0;
		this.userID = 0;
		this.pid = 0;
		this.isRunning = false;
		this.totalPrivateDirtyMemory = 0;
		this.totalPssMemory = 0;
		this.totalSharedDirtyMemory = 0;
	}
	
	/**
	 * 设置应用名称
	 * @param label
	 */
	public void setAppLabel(String label)
	{
		this.appLabel = label;
	}
	
	/**
	 * 返回应用名称
	 * @return
	 */
	public String getAppLabel()
	{
		return this.appLabel;
	}
	
	/**
	 * 设置应用图标
	 * @param appicon
	 */
	public void setAppIcon(Drawable appicon)
	{
		this.appIcon = appicon;
	}
	
	/**
	 * 返回应用图标
	 * @return
	 */
	public Drawable getAppIcon()
	{
		return this.appIcon;
	}
	
	/**
	 * 设置应用是否处于运行状态
	 * @param running
	 */
	public void setIsRunning(boolean running)
	{
		this.isRunning = running;
	}
	
	/**
	 * 返回应用是否处于运行状态
	 * @return
	 */
	public boolean getIsRunning()
	{
		return this.isRunning;
	}
	
	/**
	 * 设置进程id
	 * @param pid
	 */
	public void setPID(int pid)
	{
		this.pid = pid;
	}
	
	/**
	 * 返回进程id
	 * @return
	 */
	public int getPID()
	{
		return this.pid;
	}
	
	public void setPrivateDirtyMemory(int memory)
	{
		this.totalPrivateDirtyMemory = memory;
	}
	
	public int getPrivateDirtyMemory()
	{
		return this.totalPrivateDirtyMemory;
	}
	
	public void setPssMemory(int memory)
	{
		this.totalPssMemory = memory;
	}
	
	public int getPssMemory()
	{
		return this.totalPssMemory;
	}
	
	public void setSharedDirtyMemory(int memory)
	{
		this.totalSharedDirtyMemory = memory;
	}
	
	public int getSharedDirtyMemory()
	{
		return this.totalSharedDirtyMemory;
	}
	
	/**
	 * 设置应用名称
	 * @param name
	 */
	public void setAppName(String name)
	{
		this.appName = name;
	}
	
	/**
	 * 返回应用名称
	 * @return
	 */
	public String getAppName()
	{
		return this.appName;
	}
	
	/**
	 * 设置应用的权限
	 * @param permission
	 */
	public void setAppPermission(String permission)
	{
		this.appPermission = permission;
	}
	
	/**
	 * 返回应用权限
	 * @return
	 */
	public String getAppPermission()
	{
		return this.appPermission;
	}
	
	/**
	 * 设置类名
	 * @param className
	 */
	public void setClassName(String className)
	{
		this.className = className;
	}
	
	/**
	 * 返回类名
	 * @return
	 */
	public String getClassName()
	{
		return this.className;
	}
	
	/**
	 * 设置数据目录
	 * @param dataDir
	 */
	public void setDataDir(String dataDir)
	{
		this.dataDir = dataDir;
	}
	
	/**
	 * 返回数据目录
	 * @return
	 */
	public String getDataDir()
	{
		return this.dataDir;
	}
	
	/**
	 * 设置资源目录
	 * @param sourceDir
	 */
	public void setSourceDir(String sourceDir)
	{
		this.sourceDir = sourceDir;
	}
	
	/**
	 * 返回应用的资源目录
	 * @return
	 */
	public String getSourceDir()
	{
		return this.sourceDir;
	}
	
	/**
	 * 设置最小的SDK目录
	 * @param sdkVersion
	 */
	public void setTargetSdkVersion(int sdkVersion)
	{
		this.targetSdkVersion = sdkVersion;
	}
	
	/**
	 * 返回应用支持的最低SDK
	 * @return
	 */
	public int getTargetSdkVersion()
	{
		return this.targetSdkVersion;
	}
	
	/**
	 * 设置用户ID
	 * @param userid
	 */
	public void setUserID(int userid)
	{
		this.userID = userid;
	}
	
	/**
	 * 返回用户ID
	 * @return
	 */
	public int getUserID()
	{
		return this.userID;
	}
}
