//
//  WebViewController.h
//  BestPractice
//
//  Created by guanjianjun on 12-8-13.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

/********************************************************************
 *该类实现简单的浏览器，用户可以前进，后退，刷新和调转到safari
 *******************************************************************/

#import <UIKit/UIKit.h>

@interface WebViewController : UIViewController <UIWebViewDelegate>

//UIWebview
@property (nonatomic, retain) UIWebView *webView;

//用于容纳按钮的工具条
@property (nonatomic, retain) UIToolbar *toolbar;

//后退按钮
@property (nonatomic, retain) UIBarButtonItem *backPage;
//前进按钮
@property (nonatomic, retain) UIBarButtonItem *nextPage;
//刷新按钮
@property (nonatomic, retain) UIBarButtonItem *refreshPage;
//跳转到safari
@property (nonatomic, retain) UIBarButtonItem *gotoSafari;

//工具条上按钮被按下的处理事件
- (void)onBackPageClicked:(id)sender;
- (void)onNextPageClicked:(id)sender;
- (void)onRefreshPageClicked:(id)sender;
- (void)onGotoSafariClicked:(id)sender;

@end
