
使用帮助：
1.流量相关广播
1.1 重置流量数据
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic reset

1.2 计算流量
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic calc -e order total -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic calc -e order send -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic calc -e order receive -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic calc -e order last -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic calc -e order lastsend -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic calc -e order lastreceive -e casename guanjianjun -e appname com.qihoo.androidbrowser

1.3 获取流量数据
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic get -e order total -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic get -e order send -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic get -e order receive -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic get -e order last -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic get -e order lastsend -e casename guanjianjun -e appname com.qihoo.androidbrowser
> adb shell am broadcast -a com.qihoo.android.test.gettrafficstat -e traffic get -e order lastreceive -e casename guanjianjun -e appname com.qihoo.androidbrowser

1.2 

1.电量相关广播
1.1 重置电量数据
> adb shell am broadcast -a com.qihoo.android.test.getbatterystat -e battery reset

1.2 计算电量数据
> adb shell am broadcast -a com.qihoo.android.test.getbatterystat -e battery calc -e casename guanjianjun -e appname com.qihoo.androidbrowser

1.3 获取电量数据
> adb shell am broadcast -a com.qihoo.android.test.getbatterystat -e battery get -e casename guanjianjun -e appname com.qihoo.androidbrowser

