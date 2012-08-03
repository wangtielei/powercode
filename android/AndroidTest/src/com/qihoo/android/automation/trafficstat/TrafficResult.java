package com.qihoo.android.automation.trafficstat;

import com.qihoo.android.automation.application.*;
public class TrafficResult
{
	public AppInfo appInfo;
	
	public long sendBytes = 0;
	public long receiveBytes = 0;
	public long lastSend = 0;
	public long lastReceive = 0;
	public long timeStamp = 0;
	
	public AppInfo getAppInfo() {
		return appInfo;
	}
	public void setAppInfo(AppInfo appInfo) {
		this.appInfo = appInfo;
	}
	public long getSendBytes() {
		return sendBytes;
	}
	public void setSendBytes(long sendBytes) {
		this.sendBytes = sendBytes;
	}
	public long getReceiveBytes() {
		return receiveBytes;
	}
	public void setReceiveBytes(long receiveBytes) {
		this.receiveBytes = receiveBytes;
	}
	public long getLastSend() {
		return lastSend;
	}
	public void setLastSend(long lastSend) {
		this.lastSend = lastSend;
	}
	public long getLastReceive() {
		return lastReceive;
	}
	public void setLastReceive(long lastReceive) {
		this.lastReceive = lastReceive;
	}
	public long getTimeStamp() {
		return timeStamp;
	}
	public void setTimeStamp(long timeStamp) {
		this.timeStamp = timeStamp;
	}
}
