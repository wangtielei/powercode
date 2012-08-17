//
//  WebViewController.m
//  BestPractice
//
//  Created by guanjianjun on 12-8-13.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import "WebViewController.h"

// Toolbar height
#define kToolbarHeight 48

@interface WebViewController ()

@end

@implementation WebViewController

//UIWebview
@synthesize webView = _webView;

//用于容纳按钮的工具条
@synthesize toolbar = _toolbar;

//后退按钮
@synthesize backPage = _backPage;
//前进按钮
@synthesize nextPage = _nextPage;
//刷新按钮
@synthesize refreshPage = _refreshPage;
//跳转到safari
@synthesize gotoSafari = _gotoSafari;


/*******************************************************************************
 *根据nib文件创建webviewcontroller
 ******************************************************************************/
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
    }
    return self;
}

/*******************************************************************************
 *创建web view
 ******************************************************************************/
- (void)createWebView
{
    CGFloat yOffset = 0.0f;
    if (self.navigationController)
    {
        yOffset = self.navigationController.toolbar.bounds.size.height;
    }
    
    CGRect webFrame = [[UIScreen mainScreen] applicationFrame];
    webFrame.origin.y = yOffset;
    // leave room for toolbar
    webFrame.size.height -= (yOffset + kToolbarHeight);    
    self.webView = [[UIWebView alloc] initWithFrame:webFrame];
    self.webView.backgroundColor = [UIColor whiteColor];
    self.webView.scalesPageToFit = YES;
    self.webView.autoresizingMask = (UIViewAutoresizingFlexibleWidth | UIViewAutoresizingFlexibleHeight);
    self.webView.delegate = self;
    [self.view addSubview: self.webView];     
}

/*******************************************************************************
 *创建工具条
 ******************************************************************************/
- (void)createToolbar:(CGRect)webFrame
{
    CGRect toolbarFrame = CGRectMake(webFrame.origin.x, webFrame.origin.y + webFrame.size.height, webFrame.size.width, kToolbarHeight);
    self.toolbar = [[UIToolbar alloc] initWithFrame:toolbarFrame];
    self.toolbar.barStyle = UIBarStyleBlackOpaque;
    // Allow the toolbar location and size to adjust properly as the orientation changes. 
    self.toolbar.autoresizingMask = UIViewAutoresizingFlexibleWidth | UIViewAutoresizingFlexibleTopMargin;
    
    UIBarButtonItem *leftFlexibleItem = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil];
    UIBarButtonItem *rightFlexibleItem = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil];
    
    //add backPage Button
    self.backPage = [[UIBarButtonItem alloc] initWithTitle:@"Back" style:UIBarButtonItemStylePlain target:self action:@selector(onBackPageClicked:)];  
    //self.backPage.image = [UIImage imageNamed:@"ranks.png"];
    
    //add nextPage button
    self.nextPage = [[UIBarButtonItem alloc] initWithTitle:@"Next" style:UIBarButtonItemStylePlain target:self action:@selector(onNextPageClicked:)];  
    
    
    //add freshPage button
    //self.refreshPage = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(onRefreshPageClicked:)];  
    self.refreshPage = [[UIBarButtonItem alloc] initWithTitle:@"Refresh" style:UIBarButtonItemStylePlain target:self action:@selector(onRefreshPageClicked:)]; 
    
    //add safari button
    self.gotoSafari = [[UIBarButtonItem alloc] initWithTitle:@"Safari" style:UIBarButtonItemStylePlain target:self action:@selector(onGotoSafariClicked:)];  
    
    self.toolbar.items = [NSArray arrayWithObjects: leftFlexibleItem, _backPage, _nextPage, _refreshPage, _gotoSafari, rightFlexibleItem, nil];    
    [self.view addSubview:self.toolbar];
}

- (void)loadPage:(NSString*)url
{
    [self.webView loadRequest:[NSURLRequest requestWithURL:[NSURL URLWithString:url]]];
    
}

/*******************************************************************************
 *back按钮被按下
 ******************************************************************************/
- (void)onBackPageClicked:(id)sender
{
    DDLogEndMethodInfo();
}

/*******************************************************************************
 *next按钮被按下
 ******************************************************************************/
- (void)onNextPageClicked:(id)sender
{
    DDLogEndMethodInfo();
}

/*******************************************************************************
 *refresh按钮被按下
 ******************************************************************************/
- (void)onRefreshPageClicked:(id)sender
{
    DDLogEndMethodInfo();
}

/*******************************************************************************
 *safari按钮被按下
 ******************************************************************************/
- (void)onGotoSafariClicked:(id)sender
{
    DDLogEndMethodInfo();
}

/*******************************************************************************
 *重载viewDidLoad
 ******************************************************************************/
- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view.
    [self createWebView];
    [self createToolbar:self.webView.frame];
    
    //加载缺省页面
    [self loadPage:@"http://www.sina.com.cn"];
}

- (void)viewDidUnload
{
    [super viewDidUnload];
    // Release any retained subviews of the main view.
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}


#pragma mark -
#pragma mark UIWebViewDelegate

- (void)webViewDidStartLoad:(UIWebView *)webView
{
    // Starting the load, show the activity indicator in the status bar.
    [UIApplication sharedApplication].networkActivityIndicatorVisible = YES;
}


- (void)webViewDidFinishLoad:(UIWebView *)webView
{
    // Finished loading, hide the activity indicator in the status bar.
    [UIApplication sharedApplication].networkActivityIndicatorVisible = NO;
    NSURL *url = [webView.request URL];
}


- (BOOL)webView:(UIWebView *)webView shouldStartLoadWithRequest:(NSURLRequest *)request navigationType:(UIWebViewNavigationType)navigationType
{
    NSURL *url = [request URL];
    return YES;
}


- (void)webView:(UIWebView *)webView didFailLoadWithError:(NSError *)error
{
    // Load error, hide the activity indicator in the status bar.
    [UIApplication sharedApplication].networkActivityIndicatorVisible = NO;
    
    // Report the error inside the webview.
    NSString* errorString = [NSString stringWithFormat:
                             @"<html><center><font size=+5 color='red'>An error occurred:<br>%@</font></center></html>",
                             error.localizedDescription];
    [self.webView loadHTMLString:errorString baseURL:nil];
}

@end
