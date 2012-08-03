package com.qihoo.android.automation.trafficstat;

import org.apache.log4j.Logger;


import com.qihoo.android.automation.application.AppInfo;
import com.qihoo.android.automation.application.AppSniffer;
import com.qihoo.android.automation.utility.DatabaseHelper;
import android.content.Context;
import android.database.Cursor;
import android.net.TrafficStats;
import java.util.List;
import java.util.ArrayList;

public class TrafficCounter
{
	private final Logger logger = Logger.getLogger(TrafficCounter.class);
	private Context appContext = null;
	private AppSniffer appSniffer = null;
	private DatabaseHelper dbHelper = null;
	private List<TrafficResult> trafficResultList = new ArrayList<TrafficResult>();
	private List<TrafficResult> orderedResultList = new ArrayList<TrafficResult>();
	
	
	public static final String ORDER_BY_TOTAL = "orderbytotal";
	public static final String ORDER_BY_SEND = "orderbysend";
	public static final String ORDER_BY_RECEIVE = "orderbyreceive";
	public static final String ORDER_BY_LAST_RECEIVE = "orderbylastreceive";
	public static final String ORDER_BY_LAST_SEND = "orderbylastsend";
	public static final String ORDER_BY_LAST_LAST = "orderbylast";
	
	
	public TrafficCounter(Context appContext)
	{
		this.appContext = appContext;
		appSniffer = new AppSniffer(this.appContext);		
		dbHelper = new DatabaseHelper(this.appContext, "androidtest");
		dbHelper.addCreateSqlCmd("androidtest", "CREATE TABLE trafficstats (uid INT PRIMARY KEY, date TIMESTAMP, received INT, sent INT, last_received INT, last_sent INT)");
        dbHelper.addDropSqlCmd("androidtest");
	}
	
	public void getTrafficStat(String orderMode,String caseName,String appName)
	{
		orderResult(orderMode);
		printOrderedResult(caseName, appName);
	}
	
	private void orderResult(String orderMode)
	{
		orderedResultList.clear();
		
		if (orderMode.equals(ORDER_BY_TOTAL))
		{
			orderByTotal();
		}
		else if (orderMode.equals(ORDER_BY_SEND))
		{
			orderBySend();
		}
		else if (orderMode.equals(ORDER_BY_RECEIVE))
		{
			orderByReceive();
		}
		else if (orderMode.equals(ORDER_BY_LAST_RECEIVE))
		{
			orderByLastSend();
		}
		else if (orderMode.equals(ORDER_BY_LAST_SEND))
		{
			orderByLastReceive();
		}
		else if (orderMode.equals(ORDER_BY_LAST_LAST))
		{
			orderByLast();
		}
	}
	
	public void calcTrafficStat(String orderMode,String caseName,String appName)
	{
		this.resetTrafficStat();
		orderedResultList.clear();
		
		orderResult(orderMode);
		
	    printOrderedResult(caseName, appName);
	}
	
	private void orderByLast()
	{
		for (int i=0; i<trafficResultList.size(); ++i)
		{
			TrafficResult trafficResult = trafficResultList.get(i);
			
			int pos = -1;
			for (int k=0; k<orderedResultList.size(); ++k)
			{
				TrafficResult orderedResult = orderedResultList.get(k);
				
				if (orderedResult.lastReceive + orderedResult.lastSend > trafficResult.lastReceive + trafficResult.lastSend)
				{
					pos = k;
					break;
				}
			}
			
			if (pos == -1)
			{
				orderedResultList.add(trafficResult);
			}
			else
			{
				orderedResultList.add(pos, trafficResult);
			}
		}
	}
	
	private void orderByTotal()
	{
		for (int i=0; i<trafficResultList.size(); ++i)
		{
			TrafficResult trafficResult = trafficResultList.get(i);
			
			int pos = -1;
			for (int k=0; k<orderedResultList.size(); ++k)
			{
				TrafficResult orderedResult = orderedResultList.get(k);
				
				if (orderedResult.sendBytes + orderedResult.receiveBytes > trafficResult.sendBytes+trafficResult.receiveBytes)
				{
					pos = k;
					break;
				}
			}
			
			if (pos == -1)
			{
				orderedResultList.add(trafficResult);
			}
			else
			{
				orderedResultList.add(pos, trafficResult);
			}
		}
	}
	
	private void orderBySend()
	{
		for (int i=0; i<trafficResultList.size(); ++i)
		{
			TrafficResult trafficResult = trafficResultList.get(i);
			
			int pos = -1;
			for (int k=0; k<orderedResultList.size(); ++k)
			{
				TrafficResult orderedResult = orderedResultList.get(k);
				
				if (orderedResult.sendBytes > trafficResult.sendBytes)
				{
					pos = k;
					break;
				}
			}
			
			if (pos == -1)
			{
				orderedResultList.add(trafficResult);
			}
			else
			{
				orderedResultList.add(pos, trafficResult);
			}
		}
	}
	
	private void orderByReceive()
	{
		for (int i=0; i<trafficResultList.size(); ++i)
		{
			TrafficResult trafficResult = trafficResultList.get(i);
			
			int pos = -1;
			for (int k=0; k<orderedResultList.size(); ++k)
			{
				TrafficResult orderedResult = orderedResultList.get(k);
				
				if (orderedResult.receiveBytes > trafficResult.receiveBytes)
				{
					pos = k;
					break;
				}
			}
			
			if (pos == -1)
			{
				orderedResultList.add(trafficResult);
			}
			else
			{
				orderedResultList.add(pos, trafficResult);
			}
		}
	}
	
	private void orderByLastSend()
	{
		for (int i=0; i<trafficResultList.size(); ++i)
		{
			TrafficResult trafficResult = trafficResultList.get(i);
			
			int pos = -1;
			for (int k=0; k<orderedResultList.size(); ++k)
			{
				TrafficResult orderedResult = orderedResultList.get(k);
				
				if (orderedResult.lastSend > trafficResult.lastSend)
				{
					pos = k;
					break;
				}
			}
			
			if (pos == -1)
			{
				orderedResultList.add(trafficResult);
			}
			else
			{
				orderedResultList.add(pos, trafficResult);
			}
		}
	}
	
	private void orderByLastReceive()
	{
		for (int i=0; i<trafficResultList.size(); ++i)
		{
			TrafficResult trafficResult = trafficResultList.get(i);
			
			int pos = -1;
			for (int k=0; k<orderedResultList.size(); ++k)
			{
				TrafficResult orderedResult = orderedResultList.get(k);
				
				if (orderedResult.lastReceive > trafficResult.lastReceive)
				{
					pos = k;
					break;
				}
			}
			
			if (pos == -1)
			{
				orderedResultList.add(trafficResult);
			}
			else
			{
				orderedResultList.add(pos, trafficResult);
			}
		}
	}
		
	/**
	 * 重置流量统计信息
	 */
	public void resetTrafficStat()
	{
		trafficResultList.clear();
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
				
				TrafficResult trafficResult = new TrafficResult();
				trafficResult.appInfo = appInfo;
				
				String sqlCmd = "";
				if (this.loadDBData(trafficResult))
				{
					trafficResult.lastReceive = receive - trafficResult.receiveBytes;
					trafficResult.lastSend = send - trafficResult.sendBytes;
					trafficResult.sendBytes = send;
					trafficResult.receiveBytes = receive;
					trafficResult.timeStamp = System.currentTimeMillis();
					
					sqlCmd = String.format("update trafficstats set date=%d, received=%d, sent=%d, last_received=%d, last_sent=%d where uid=%d",
							trafficResult.timeStamp, trafficResult.receiveBytes, trafficResult.sendBytes,
							trafficResult.lastReceive, trafficResult.lastSend, trafficResult.appInfo.getUserID());				
				}
				else
				{
					trafficResult.lastReceive = 0;
					trafficResult.lastSend = 0;
					trafficResult.sendBytes = send;
					trafficResult.receiveBytes = receive;
					trafficResult.timeStamp = System.currentTimeMillis();
			
					if (send + receive <= 0)
					{
						continue;
					}					
					sqlCmd = String.format("insert into trafficstats (uid, date, received, sent, last_received, last_sent) values (%d, %d, %d, %d, %d, %d)",
							trafficResult.appInfo.getUserID(), trafficResult.timeStamp, trafficResult.receiveBytes, trafficResult.sendBytes,
							trafficResult.lastReceive, trafficResult.lastSend);	
				}
				
				dbHelper.execSql(sqlCmd);
				trafficResultList.add(trafficResult);
			}
			catch (Exception ex)
			{
				logger.error("calcTrafficStat get exception: " + ex.getMessage());
			}
		}
				
        logger.debug(String.format("has %d apps", appSniffer.getInstalledAppCount()));        
	}
		
	public boolean loadDBData(TrafficResult trafficResult)
	{
		String sqlCmd = String.format("select date,received,sent,last_received,last_sent from trafficstats where uid=%d", trafficResult.appInfo.getUserID());
		Cursor trafficCursor = dbHelper.queryData(sqlCmd);
		if (trafficCursor.getCount() != 1)
			return false;
		trafficCursor.moveToFirst();
		
		trafficResult.timeStamp = trafficCursor.getLong(0);
		trafficResult.receiveBytes = trafficCursor.getLong(1);
		trafficResult.sendBytes = trafficCursor.getLong(2);
		trafficResult.lastReceive = trafficCursor.getLong(3);
		trafficResult.lastSend = trafficCursor.getLong(4);
		
		trafficCursor.close();
		
		return true;
	}	
	
	public void printOrderedResult(String caseName,String appName)
	{
		//logger.error("==========begin trafficstat==========");
		//logger.error("inxex | appname | uid | timestamp | Total | received | sent | last_received | last_sent |");
		
		int k=0;
		for (int i=orderedResultList.size()-1; i>=0; --i)
		{
			TrafficResult trafficResult = orderedResultList.get(i);
			if (appName.length() > 0)
			{
				if (appName.equalsIgnoreCase(trafficResult.getAppInfo().getAppName()))
				{
					logger.error(String.format("|traffic|special|%s|%s|%s|%d|%d|%d|%d|%d|%d|%d|", 
							appName, 
							caseName, 
							trafficResult.appInfo.getAppLabel(), 
							trafficResult.appInfo.getUserID(),
							trafficResult.timeStamp, 
							trafficResult.receiveBytes + trafficResult.sendBytes,
							trafficResult.receiveBytes, 
							trafficResult.sendBytes,
							trafficResult.lastReceive, 
							trafficResult.lastSend));
					return;
				}
			}
			else
			{
				logger.error(String.format("|traffic|%d|%s|%s|%d|%d|%d|%d|%d|%d|%d|", 
						++k, 
						trafficResult.appInfo.getAppName(),
						trafficResult.appInfo.getAppLabel(), 
						trafficResult.appInfo.getUserID(),
						trafficResult.timeStamp, 
						trafficResult.receiveBytes + trafficResult.sendBytes,
						trafficResult.receiveBytes, 
						trafficResult.sendBytes,
						trafficResult.lastReceive, 
						trafficResult.lastSend));
			}
		}
		//logger.error("==========end trafficstat==========");
	}
}
