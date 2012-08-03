package com.qihoo.android.automation.utility;

/***********************************************
 * This class is wrapper for SQLite database.
 * it provide database access interface. 
 */

import java.util.Map;
import java.util.HashMap;
import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper; 
import android.database.Cursor;
import org.apache.log4j.Logger;

public class DatabaseHelper extends SQLiteOpenHelper
{
	private final Logger logger = Logger.getLogger(DatabaseHelper.class);
	
	//store need to create table sql command
	private Map<String, String> createTables = new HashMap<String, String>();
	
	//store need to drop table names
	private Map<String, String> dropTables = new HashMap<String, String>();
	
	/**
	 * 
	 * @param paramContext
	 * @param dbName
	 */
	public DatabaseHelper(Context paramContext, String dbName)
	{
		super(paramContext, dbName, null, 1);
	}
	
	/**
	 * 
	 * @param paramContext
	 * @param dbName
	 * @param version
	 */
	public DatabaseHelper(Context paramContext, String dbName, int version)
	{
		super(paramContext, dbName, null, version);
	}
	
	/**
	 * add create table sql command
	 * @param sqlcmd
	 */
	public void addCreateSqlCmd(String tableName, String sqlcmd)
	{
		createTables.put(tableName, sqlcmd);
	}
	
	/**
	 * add drop table sql command
	 * @param sqlcmd
	 */
	public void addDropSqlCmd(String tableName)
	{
		dropTables.put(tableName, "");
	}
	
	/**
	 * create table
	 */
	@Override
    public void onCreate(SQLiteDatabase db)
	{     
		logger.debug("enter onCreate");
		
		//execute all table create command
		for(Object obj : createTables.keySet()) 
		{     
		    String sqlCmd = createTables.get(obj).toString();   
		    db.execSQL(sqlCmd);
		}
    }
    
	/**
	 * drop table
	 */
    @Override    
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) 
    {     
        logger.debug("enter onUpgrade()");
        //execute all drop table command
        for(Object obj : dropTables.keySet()) 
        {
        	String sqlCmd = String.format("DROP TABLE IF EXISTS %s", obj.toString());
        	logger.debug("drop table: " + sqlCmd);
        	
        	db.execSQL(sqlCmd);
        }
        onCreate(db);
    }     
    
    /**
     * do nothing
     */
	@Override    
	public void onOpen(SQLiteDatabase db) 
	{     
		logger.debug("enter onOpen()");
        super.onOpen(db);
    }    
	
	/**
	 * execute sql command;
	 * @param sqlCmd
	 * @return
	 */
	public boolean execSql(String sqlCmd)
	{
		logger.debug("enter execSql(), sqlCmd: " + sqlCmd);
		try
		{
			SQLiteDatabase db = this.getWritableDatabase();
			db.execSQL(sqlCmd);
		}
		catch(Exception ex)
		{
			logger.error("exec sql command exception: " + ex.getMessage());
			return false;
		}
		
		return true;
	}
	
	/**
	 * query data by sql command
	 * @param sqlCmd
	 * @return
	 */
	public Cursor queryData(String sqlCmd)
	{
		logger.debug("enter queryData(), sqlCmd: " + sqlCmd);
		SQLiteDatabase db = this.getReadableDatabase();
		
		return db.rawQuery(sqlCmd, null);
	}
}
