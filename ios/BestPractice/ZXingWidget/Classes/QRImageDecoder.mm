//
//  QRImageDecoder.m
//  ZXingWidget
//
//  Created by guanjianjun on 12-8-7.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import "QRImageDecoder.h"
#import "TwoDDecoderResult.h"

@implementation QRImageDecoder

@synthesize notifyDelegate = _notifyDelegate;

- (id)init
{
    self = [super init];
    
    if (self)
    {
        qrcodeReader = [[QRCodeReader alloc] init];
        NSSet *readers = [[NSSet alloc ] initWithObjects:qrcodeReader,nil];        
        qrDecoder = [[Decoder alloc] init];
        qrDecoder.readers = readers;
        qrDecoder.delegate = self;
        
        [readers release];
    }
    
    return self;
}

- (void)dealloc
{
    if (qrcodeReader)
    {
        [qrcodeReader release];
    }
    
    if (qrDecoder)
    {
        [qrDecoder release];
    }
    
    [super dealloc];
}

//同步解码一张图片，如果解码成功则返回字符串，否则返回nil
- (NSString *)syncDecode:(UIImage*)srcImage
{
    return [qrDecoder syncDecodeImage:srcImage];
}

//同步解码一张图片的指定区域，如果解码成功则返回字符串，否则返回nil
- (NSString *)syncDecode:(UIImage*)srcImage cropRect:(CGRect)rc
{
    return [qrDecoder syncDecodeImage:srcImage cropRect:rc];
}

//异步解码一张图片，如果解码成功则返回字符串，否则返回nil
- (BOOL)asyncDecode:(UIImage*)srcImage
{
    return [qrDecoder decodeImage:srcImage];
}

//异步解码一张图片的指定区域，如果解码成功则返回字符串，否则返回nil
- (BOOL)asyncDecode:(UIImage*)srcImage cropRect:(CGRect)rc
{
    return [qrDecoder decodeImage:srcImage cropRect:rc];
}

//======Begin 实现 DecoderDelegate 的方法======//
- (void)decoder:(Decoder *)decoder willDecodeImage:(UIImage *)image usingSubset:(UIImage *)subset
{
    //do nothing
}

- (void)decoder:(Decoder *)decoder didDecodeImage:(UIImage *)image usingSubset:(UIImage *)subset withResult:(TwoDDecoderResult *)twoDResult
{
    if ([_notifyDelegate respondsToSelector:@selector(successDecodeImage:)]) 
    {
        [_notifyDelegate successDecodeImage:[[twoDResult text] copy]];
    }
}

- (void)decoder:(Decoder *)decoder failedToDecodeImage:(UIImage *)image usingSubset:(UIImage *)subset reason:(NSString *)reason
{
    if ([_notifyDelegate respondsToSelector:@selector(failDecodeImage:)]) 
    {
        [_notifyDelegate successDecodeImage:reason];
    }
}

- (void)decoder:(Decoder *)decoder foundPossibleResultPoint:(CGPoint)point
{
    //do nothing
}
//======End 实现 DecoderDelegate 的方法======//

@end
