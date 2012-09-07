//
//  BrowserController.h
//  newsyc
//
//  Created by Grant Paul on 3/7/11.
//  Copyright 2011 Xuzz Productions, LLC. All rights reserved.
//

#import "InstapaperRequest.h"
#import "InstapaperSession.h"
#import "InstapaperLoginController.h"
#import "ProgressHUD.h"
#import "ActivityIndicatorItem.h"
#import "BarButtonItem.h"

#import <MessageUI/MFMailComposeViewController.h>

#define kReadabilityBookmarkletCode @"(function(){window.baseUrl='https://www.readability.com';window.readabilityToken='';var s=document.createElement('script');s.setAttribute('type','text/javascript');s.setAttribute('charset','UTF-8');s.setAttribute('src',baseUrl+'/bookmarklet/read.js');document.documentElement.appendChild(s);})()"

@interface BrowserController : UIViewController <UIWebViewDelegate, UIActionSheetDelegate, UIAlertViewDelegate, MFMailComposeViewControllerDelegate> {
    UIWebView *webview;
    
    NSURL *rootURL;
    NSURL *currentURL;
    NSURL *externalURL;
    
    UIToolbar *toolbar;
    BarButtonItem *toolbarItem;
    
    BarButtonItem *backItem;
    BarButtonItem *forwardItem;
    ActivityIndicatorItem *loadingItem;
    BarButtonItem *refreshItem;
    BarButtonItem *shareItem;
    BarButtonItem *spacerItem;
    BarButtonItem *readabilityItem;
    
    NSInteger networkRetainCount;
}

@property (nonatomic, copy) NSURL *currentURL;

- (id)initWithURL:(NSURL *)url;

@end
