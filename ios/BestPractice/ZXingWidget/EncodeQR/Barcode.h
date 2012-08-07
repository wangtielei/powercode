//
//  Barcode.h
//  iOSKuapay
//
//  Created by Patrick Hogan on 12/5/11.
//  Copyright (c) 2011 Kuapay LLC. All rights reserved.
//
#import "UIKit/UIImage.h"

/*
 使用非常简单，只需要创建一个Barcode实例，设置字符串给他即可生成一个UIImage
 imageView = [[UIImageView alloc] initWithFrame:CGRectZero];
 imageView.frame = CGRectMake(2, 20, 100, 100);
 NSString *code = @"http://www.ioszhushou.com/Appinstaller.ipa";    
 Barcode *barcode = [[Barcode alloc] init];        
 [barcode setupQRCode:code];
 //[barcode setupBarcodes:code];
 imageView.image = barcode.qRBarcode;
 [self.view addSubview:imageView];

*/


@class Barcode;

typedef enum
{
    CODE_128,
    EAN_13
} OneDimCodeType;

typedef enum
{
    EAN8 = 8,
    EAN13 = 13
} BarcodeType;


@interface Barcode : NSObject
{
    NSArray *encoding, *first, *code128Encoding;
}

@property (nonatomic, retain) UIImage *oneDimBarcode;
@property (nonatomic, retain) UIImage *qRBarcode;

@property (nonatomic, copy) NSString *oneDimCode;

-(void)setupBarcodes:(NSString *)code;
-(void)setupQRCode:(NSString *)code;
-(void)setupOneDimBarcode:(NSString *)code type:(OneDimCodeType)type;

@end
