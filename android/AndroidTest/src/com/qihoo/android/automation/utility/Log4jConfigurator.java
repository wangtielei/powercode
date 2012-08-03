package com.qihoo.android.automation.utility;

/*
 * this class is used to configure log4j instance. Normally, 
 * a application should init log configuration
 * at startup and only one time during its lifetime.
 * 
 * by default, log will be output into file and logcat.
 * 
 * because log4j will use sd card, so you must add permission apply in 
 * AndroidManifest.xml file.
 * <uses-permission android:name="android.permission.MOUNT_UNMOUNT_FILESYSTEMS" />
 * <uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
    
 // Field descriptor #56 Lorg/apache/log4j/Level;
  public static final org.apache.log4j.Level FATAL;
  
  // Field descriptor #56 Lorg/apache/log4j/Level;
  public static final org.apache.log4j.Level ERROR;
  
  // Field descriptor #56 Lorg/apache/log4j/Level;
  public static final org.apache.log4j.Level WARN;
  
  // Field descriptor #56 Lorg/apache/log4j/Level;
  public static final org.apache.log4j.Level INFO;
  
  // Field descriptor #56 Lorg/apache/log4j/Level;
  public static final org.apache.log4j.Level DEBUG;
  
  // Field descriptor #56 Lorg/apache/log4j/Level;
  public static final org.apache.log4j.Level TRACE;
  
  // Field descriptor #56 Lorg/apache/log4j/Level;
  public static final org.apache.log4j.Level ALL;
 */

import org.apache.log4j.Level;
import android.os.Environment;
import de.mindpipe.android.logging.log4j.LogConfigurator;

public class Log4jConfigurator
{
	//log tree name
	static final String logName = "qihoo.android.automation";
	
	//log formating
	static final String logPattern = "[%d][%p::%C::%M::%L] - %m%n";
	
	//log configurator instance, default is null.
	static LogConfigurator logConfigurator = null;
	
	
	//////////////////////////////////////////////////////////////
	//Functions
	/////////////////////////////////////////////////////////////
	
	/**
	 * configure log
	 * @param level
	 */
	private static void configureLog(Level level)
	{
		logConfigurator.setRootLevel(level);
		logConfigurator.setLevel(logName, level);
		logConfigurator.setFilePattern(logPattern);
		logConfigurator.configure();
	}
	
	private static String createFilePath(String fileName)
	{
		return Environment.getExternalStorageDirectory() + "/" + fileName;
	}
	/**
	 * init log configuration, with it the root level is Level.ALL and default ouput level is Level.TRACE.
	 * @param fileName
	 */
	public static void initLog(String fileName)
	{
		if (!isLogInit())
		{
			logConfigurator = new LogConfigurator(createFilePath(fileName));
			configureLog(Level.TRACE);
		}
	}
	
	/**
	 * init log configuration with specific log level.
	 * @param fileName
	 * @param level
	 */
	public static void initLogWithLevel(String fileName, Level level)
	{
		if (!isLogInit())
		{
			logConfigurator = new LogConfigurator(createFilePath(fileName));
			configureLog(level);
		}
	}
	
	
	/**
	 * 
	 * @return: true--if log is initialized, or return false
	 */
	public static boolean isLogInit()
	{
		if (logConfigurator == null)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
