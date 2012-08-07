//
//  QRImageDecoder.h
//  ZXingWidget
//
//  Created by guanjianjun on 12-8-7.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//
/**
 * 该类提供直接解析包含有二维码的图片功能.
 * 解析方法包含：1）同步解析直接返回字符串；2）异步解析；
 */

#import <Foundation/Foundation.h>
#import "Decoder.h"
#import "QRCodeReader.h"

@protocol QRDecodeDelegate<NSObject>

@optional
//解码成功时被调用，decodeResult为解码结果
- (void)successDecodeImage:(NSString*)decodeResult;

//解码失败时被调用，decodeResult为解码结果
- (void)failDecodeImage:(NSString*)reason;

@end


@interface QRImageDecoder : NSObject<DecoderDelegate>
{
    //二维码解码实例
    Decoder *qrDecoder;
    
    //二位嘛解码算法
    QRCodeReader *qrcodeReader;
}

//解析完成后通知对象
@property(nonatomic, retain) id<QRDecodeDelegate> notifyDelegate;

//同步解码一张图片，如果解码成功则返回字符串，否则返回nil
- (NSString *)syncDecode:(UIImage*)srcImage;

//同步解码一张图片的指定区域，如果解码成功则返回字符串，否则返回nil
- (NSString *)syncDecode:(UIImage*)srcImage cropRect:(CGRect)rc;

//异步解码一张图片，如果解码成功则返回字符串，否则返回nil
- (BOOL)asyncDecode:(UIImage*)srcImage;

//异步解码一张图片的指定区域，如果解码成功则返回字符串，否则返回nil
- (BOOL)asyncDecode:(UIImage*)srcImage cropRect:(CGRect)rc;

@end
