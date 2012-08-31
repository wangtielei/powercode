//
//  UIView+Origami.m
//  origami
//
//  Created by XY Feng on 4/6/12.
//  Copyright (c) 2012 Xiaoyang Feng. All rights reserved.
//
//  Permission is hereby granted, free of charge, to any person
//  obtaining a copy of this software and associated documentation
//  files (the "Software"), to deal in the Software without
//  restriction, including without limitation the rights to use,
//  copy, modify, merge, publish, distribute, sublicense, and/or sell
//  copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following
//  conditions:
//
//  The above copyright notice and this permission notice shall be
//  included in all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
//  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
//  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
//  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
//  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
//  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
//  OTHER DEALINGS IN THE SOFTWARE.

#import "Origami.h"

KeyframeParametricBlock openFunction = ^double(double time) {
    return sin(time*M_PI_2);
};
KeyframeParametricBlock closeFunction = ^double(double time) {
    return -cos(time*M_PI_2)+1;
};


@interface Origami()
{
    CFRunLoopRef currentLoop;
    //总的动画数量
    int _totalAnimation; 
    //已经完成的动画数量
    int _finishedAnimation;
    //折叠效果的layer根节点
    CALayer *_origamiLayer;
    //当前是展开还是收缩
    BOOL _isSpead;
    NSMutableArray *_joinLayers;
    NSMutableArray *_imageLayers;
    NSMutableArray *_shadowLayers;
}

@end

@implementation Origami

@synthesize backgroundImage = _backgroundImage;

-(id)init
{
    self = [super init];
    
    if (self)
    {
        self->_totalAnimation = 0;
        self->_finishedAnimation = 0;
        self->_backgroundImage = nil;
        self->_origamiLayer=nil;
        self->_isSpead = TRUE;
        self->_joinLayers = [[NSMutableArray alloc] init];
        self->_imageLayers = [[NSMutableArray alloc] init];
        self->_shadowLayers = [[NSMutableArray alloc] init];
    }
    
    return self;
}

/********************************************************************
 *从图片中取得一块区域，然后生成layer
 *******************************************************************/
-(CATransformLayer *)transformLayerFromImage:(UIImage *)image 
                                       Frame:(CGRect)frame 
                                   Direction:(XYOrigamiDirection)direction 
                                    Duration:(CGFloat)duration 
                                 AnchorPiont:(CGPoint)anchorPoint 
                                  StartAngle:(double)start 
                                    EndAngle:(double)end;
{
    CATransformLayer *jointLayer = [CATransformLayer layer];
    jointLayer.anchorPoint = anchorPoint;
    CGFloat layerWidth, layerHeight;
    if (direction == XYOrigamiDirectionFromLeft) //from left to right
    {
        layerWidth = image.size.width - frame.origin.x;
        jointLayer.frame = CGRectMake(0, 0, layerWidth, frame.size.height);
        if (frame.origin.x) 
        {
            jointLayer.position = CGPointMake(frame.size.width, frame.size.height/2);
        }
        else 
        {
            jointLayer.position = CGPointMake(0, frame.size.height/2);
        }
    }
    else if (direction == XYOrigamiDirectionFromRight) //from right to left
    { //from right to left
        layerWidth = frame.origin.x + frame.size.width;
        jointLayer.frame = CGRectMake(0, 0, layerWidth, frame.size.height);
        jointLayer.position = CGPointMake(layerWidth, frame.size.height/2);
    }
    else if (direction == XYOrigamiDirectionFromTop) //from top to bottom
    {
        layerHeight = image.size.height - frame.origin.y;
        jointLayer.frame = CGRectMake(0, 0, frame.size.width, layerHeight);
        if (frame.origin.y) 
        {
            jointLayer.position = CGPointMake(frame.size.width/2, frame.size.height);
        }
        else 
        {
            jointLayer.position = CGPointMake(frame.size.width/2, 0);
        }
    }
    else if (direction == XYOrigamiDirectionFromBottom) //from bottom to top
    {
        //from right to left
        layerHeight = frame.origin.y + frame.size.height;
        jointLayer.frame = CGRectMake(0, 0, frame.size.width, layerHeight);
        jointLayer.position = CGPointMake(frame.size.width/2, layerHeight);
    }

    //map image onto transform layer
    CALayer *imageLayer = [CALayer layer];
    imageLayer.frame = CGRectMake(0, 0, frame.size.width, frame.size.height);
    imageLayer.anchorPoint = anchorPoint;
    if (direction == XYOrigamiDirectionFromLeft || direction == XYOrigamiDirectionFromRight) 
    {
        imageLayer.position = CGPointMake(layerWidth*anchorPoint.x, frame.size.height/2);
    }
    else if (direction == XYOrigamiDirectionFromBottom || direction == XYOrigamiDirectionFromTop) 
    {
        imageLayer.position = CGPointMake(frame.size.width/2, layerHeight*anchorPoint.y);
    }
    [jointLayer addSublayer:imageLayer];
    CGRect cropFrame = frame;
    cropFrame.origin.x *= image.scale;
    cropFrame.origin.y *= image.scale;
    cropFrame.size.height *= image.scale;
    cropFrame.size.width *= image.scale;
    CGImageRef imageCrop = CGImageCreateWithImageInRect(image.CGImage, cropFrame);
    imageLayer.contents = (__bridge id)imageCrop;
    imageLayer.backgroundColor = [UIColor clearColor].CGColor;
    CGImageRelease(imageCrop);
    
    //add shadow
    double shadowAniOpacity;
    CAGradientLayer *shadowLayer = [CAGradientLayer layer];
    shadowLayer.frame = imageLayer.bounds;
    shadowLayer.backgroundColor = [UIColor darkGrayColor].CGColor;
    shadowLayer.opacity = 0.0;
    shadowLayer.colors = [NSArray arrayWithObjects:(id)[UIColor blackColor].CGColor, (id)[UIColor whiteColor].CGColor, nil];
    if (direction == XYOrigamiDirectionFromLeft || direction == XYOrigamiDirectionFromRight) 
    {
        NSInteger index = frame.origin.x/frame.size.width;
        if (index%2) 
        {
            shadowLayer.startPoint = CGPointMake(0, 0.5);
            shadowLayer.endPoint = CGPointMake(1, 0.5);
            shadowAniOpacity = (anchorPoint.x)?0.24:0.32;
        }
        else 
        {
            shadowLayer.startPoint = CGPointMake(1, 0.5);
            shadowLayer.endPoint = CGPointMake(0, 0.5);
            shadowAniOpacity = (anchorPoint.x)?0.32:0.24;
        }
    }
    else if (direction == XYOrigamiDirectionFromBottom || direction == XYOrigamiDirectionFromTop) 
    {
        NSInteger index = frame.origin.y/frame.size.height;
        if (index%2) 
        {
            shadowLayer.startPoint = CGPointMake(0.5, 0);
            shadowLayer.endPoint = CGPointMake(0.5, 1);
            shadowAniOpacity = (anchorPoint.y)?0.32:0.24;
        }
        else 
        {
            shadowLayer.startPoint = CGPointMake(0.5, 1);
            shadowLayer.endPoint = CGPointMake(0.5, 0);
            shadowAniOpacity = (anchorPoint.y)?0.24:0.32;
        }
    }
    
    [imageLayer addSublayer:shadowLayer];
    
    //animate open/close animation
    NSString *rotationType = @"transform.rotation.y";
    if (direction == XYOrigamiDirectionFromLeft || direction == XYOrigamiDirectionFromRight) 
    {
        rotationType = @"transform.rotation.y";
    }
    else if (direction == XYOrigamiDirectionFromBottom || direction == XYOrigamiDirectionFromTop)
    {
        rotationType = @"transform.rotation.x";
    }
    CABasicAnimation* animation = [CABasicAnimation animationWithKeyPath:rotationType];
    [animation setDuration:duration];
    [animation setFromValue:[NSNumber numberWithDouble:start]];
    [animation setToValue:[NSNumber numberWithDouble:end]];
    [animation setRemovedOnCompletion:YES];
    [jointLayer addAnimation:animation forKey:@"jointAnimation"];
    animation = nil;
    
    //animate shadow opacity
    animation = [CABasicAnimation animationWithKeyPath:@"opacity"];
    [animation setDuration:duration];
    [animation setFromValue:[NSNumber numberWithDouble:(start)?shadowAniOpacity:0]];
    [animation setToValue:[NSNumber numberWithDouble:(start)?0:shadowAniOpacity]];
    [animation setRemovedOnCompletion:YES];
    animation.delegate = self;
    [shadowLayer addAnimation:animation forKey:nil];
    
    [self->_joinLayers addObject:jointLayer];
    [self->_imageLayers addObject:imageLayer];
    [self->_shadowLayers addObject:shadowLayer];
    
    return jointLayer;
}

/********************************************************************
 *根据折痕矩形判断动画方向
 *******************************************************************/
-(XYOrigamiDirection)calcDirectionWithFrames:(NSArray*)foldFrames
{
    if (foldFrames.count <= 1)
        return XYOrigamiDirectionFromLeft; //如果只有1折则默认为从左到右
    
    CGRect first = [[foldFrames objectAtIndex:0] CGRectValue];
    CGRect second = [[foldFrames objectAtIndex:1] CGRectValue];
    
    //垂直排列的
    if (first.origin.x == second.origin.x && first.origin.y != second.origin.y)
    {
        //从上到下
        if (first.origin.y < second.origin.y)
        {
            return XYOrigamiDirectionFromTop;
        }
        else 
        {
            return XYOrigamiDirectionFromBottom;
        }
    }
    else if (first.origin.x != second.origin.x && first.origin.y == second.origin.y)
    {
        //从左到右
        if (first.origin.x < second.origin.x)
        {
            return XYOrigamiDirectionFromLeft;
        }
        else 
        {
            return XYOrigamiDirectionFromRight;
        }
    }
    
    return XYOrigamiDirectionFromLeft;
}

/********************************************************************
 *显示折叠效果，从收起到展开状态
 *******************************************************************/
-(void)showOrigamiTransitionWith:(UIView *)view 
                    NumberOfFolds:(NSInteger)folds 
                         Duration:(CGFloat)duration
                        Direction:(XYOrigamiDirection)direction
{
    [self showOrigamiTransitionWith:view
                         NumberOfFolds:folds
                              Duration:duration
                             Direction:direction
                            completion:nil];
}

-(NSMutableArray*)splitFoldFramesWith:(UIView *)view
                              NumberOfFolds:(NSInteger)folds
                                  Direction:(XYOrigamiDirection)direction
{
    NSMutableArray *foldFrames = [[NSMutableArray alloc] init];
    
    CGFloat frameWidth = view.bounds.size.width;
    CGFloat frameHeight = view.bounds.size.height;
    
    CGFloat foldWidth = frameWidth/(folds*2);
    CGFloat foldHeight = frameHeight/(folds*2);
    for (int b=0; b < folds*2; b++) 
    {
        CGRect imageFrame;
        if (direction == XYOrigamiDirectionFromRight) 
        {
            imageFrame = CGRectMake(frameWidth-(b+1)*foldWidth, 0, foldWidth, frameHeight);
        }
        else if (direction == XYOrigamiDirectionFromLeft)
        {
            imageFrame = CGRectMake(b*foldWidth, 0, foldWidth, frameHeight);
        }
        else if (direction == XYOrigamiDirectionFromTop)
        {
            imageFrame = CGRectMake(0, b*foldHeight, frameWidth, foldHeight);
        }
        else if (direction == XYOrigamiDirectionFromBottom)
        {
            imageFrame = CGRectMake(0, frameHeight-(b+1)*foldHeight, frameWidth, foldHeight);
        }
        [foldFrames addObject:[NSValue valueWithCGRect:imageFrame]];
    }

    return foldFrames;
}
/********************************************************************
 *显示折叠效果，从收起到展开状态
 *如果completion函数不为nil，则整个动画结束后才会调用completion
 *******************************************************************/
-(void)showOrigamiTransitionWith:(UIView *)view      //需要折叠的视图
                    NumberOfFolds:(NSInteger)folds    //表示有几折
                         Duration:(CGFloat)duration   //展开时间(秒)
                        Direction:(XYOrigamiDirection)direction //展开方向
                       completion:(void (^)(BOOL finished))completion
{
    NSMutableArray *foldFrames = [self splitFoldFramesWith:view NumberOfFolds:folds Direction:direction];
    [self origamiTransitionWith:view FoldFrames:foldFrames Duration:duration Spread:TRUE completion:completion];
}

/********************************************************************
 *显示折叠效果，从收起到展开状态
 *view:需要折叠的视图;
 *foldFrames:指定的折痕矩形;
 *duration:动画显示时间;
 *completion:动画结束后执行的回调函数，可以为nil，如果completion函数不为nil，
 *则整个动画结束后才会调用completion
 *******************************************************************/
-(void)showOrigamiTransitionWith:(UIView *)view 
                      FoldFrames:(NSMutableArray*)foldFrames
                        Duration:(CGFloat)duration
                      completion:(void (^)(BOOL finished))completion
{
    [self origamiTransitionWith:view FoldFrames:foldFrames Duration:duration Spread:TRUE completion:completion];
}



/********************************************************************
 *显示折叠效果，从展开到收起状态
 *******************************************************************/
-(void)hideOrigamiTransitionWith:(UIView *)view
                    NumberOfFolds:(NSInteger)folds
                         Duration:(CGFloat)duration
                        Direction:(XYOrigamiDirection)direction
{
    [self hideOrigamiTransitionWith:(UIView *)view
                    NumberOfFolds:(NSInteger)folds
                        Duration:(CGFloat)duration
                        Direction:(XYOrigamiDirection)direction
                            completion:nil];
}

/********************************************************************
 *显示折叠效果，从展开到收起状态
 *如果completion函数不为nil，则整个动画结束后才会调用completion
 *******************************************************************/
-(void)hideOrigamiTransitionWith:(UIView *)view
                    NumberOfFolds:(NSInteger)folds
                         Duration:(CGFloat)duration
                        Direction:(XYOrigamiDirection)direction
                       completion:(void (^)(BOOL finished))completion
{    
    NSMutableArray *foldFrames = [self splitFoldFramesWith:view NumberOfFolds:folds Direction:direction];
    [self origamiTransitionWith:view FoldFrames:foldFrames Duration:duration Spread:FALSE completion:completion];
}
 
/********************************************************************
 *显示折叠效果，从展开到收起状态
 *如果completion函数不为nil，则整个动画结束后才会调用completion
 *view:需要折叠的视图;
 *foldFrames:指定的折痕矩形;
 *duration:动画显示时间;
 *******************************************************************/
-(void)hideOrigamiTransitionWith:(UIView *)view
                      FoldFrames:(NSMutableArray*)foldFrames
                        Duration:(CGFloat)duration
                      completion:(void (^)(BOOL finished))completion
{
    [self origamiTransitionWith:view FoldFrames:foldFrames Duration:duration Spread:FALSE completion:completion];
}

/********************************************************************
 *显示折叠效果，从展开到收起状态
 *如果completion函数不为nil，则整个动画结束后才会调用completion
 *view:需要折叠的视图;
 *foldFrames:指定的折痕矩形;
 *duration:动画显示时间;
 *isSpread:true--表示展开，false--表示收缩
 *******************************************************************/
-(void)origamiTransitionWith:(UIView *)view
                      FoldFrames:(NSMutableArray*)foldFrames
                        Duration:(CGFloat)duration
                        Spread:(BOOL)isSpread
                      completion:(void (^)(BOOL finished))completion
{    
    //根据折痕矩形数组判断方向
    XYOrigamiDirection direction = [self calcDirectionWithFrames:foldFrames];
    self->_isSpead = isSpread;
    
    //set frame
    CGPoint anchorPoint;
    if (direction == XYOrigamiDirectionFromRight) 
    { 
        anchorPoint = CGPointMake(1, 0.5);
    }
    else if (direction == XYOrigamiDirectionFromLeft)
    {
        
        anchorPoint = CGPointMake(0, 0.5);
    }
    else if (direction == XYOrigamiDirectionFromTop)
    {
        
        anchorPoint = CGPointMake(0.5, 0);
    }
    else if (direction == XYOrigamiDirectionFromBottom)
    {
        anchorPoint = CGPointMake(0.5, 1);
    }
    
    UIImage *viewSnapShot = [Origami imageFromView:view];
    //set 3D depth
    CATransform3D transform = CATransform3DIdentity;
    transform.m34 = -1.0/800.0;
    self->_origamiLayer = [CALayer layer];
    _origamiLayer.frame = view.bounds;
    _origamiLayer.backgroundColor = [UIColor clearColor].CGColor;//[UIColor colorWithWhite:0.2 alpha:1].CGColor;
    
    if (self.backgroundImage)
    {
        _origamiLayer.contents = (__bridge id)self.backgroundImage.CGImage;
    }
    
    _origamiLayer.sublayerTransform = transform;
    [view.layer addSublayer:_origamiLayer];
    
    self->currentLoop = nil;
    if (completion)
    {
        self->currentLoop = CFRunLoopGetCurrent();
    }
    self->_totalAnimation = foldFrames.count;
    self->_finishedAnimation = 0;
    
    //setup rotation angle
    double angle;
    //CGFloat frameWidth = view.bounds.size.width;
    //CGFloat frameHeight = view.bounds.size.height;
    CALayer *prevLayer = _origamiLayer;
    for (int b=0; b < foldFrames.count; b++) 
    {
        CGRect imageFrame;
        if (direction == XYOrigamiDirectionFromRight) 
        {
            if(b == 0)
                angle = -M_PI_2;
            else 
            {
                if (b%2)
                    angle = M_PI;
                else
                    angle = -M_PI;
            }
            imageFrame = [[foldFrames objectAtIndex:(foldFrames.count - 1 - b)] CGRectValue];
        }
        else if (direction == XYOrigamiDirectionFromLeft)
        {
            if(b == 0)
                angle = M_PI_2;
            else 
            {
                if (b%2)
                    angle = -M_PI;
                else
                    angle = M_PI;
            }
            imageFrame = [[foldFrames objectAtIndex:b] CGRectValue];
        }
        else if (direction == XYOrigamiDirectionFromTop)
        {
            if(b == 0)
                angle = -M_PI_2;
            else 
            {
                if (b%2)
                    angle = M_PI;
                else
                    angle = -M_PI;
            }
            imageFrame = [[foldFrames objectAtIndex:b] CGRectValue];
        }
        else if (direction == XYOrigamiDirectionFromBottom)
        {
            if(b == 0)
                angle = M_PI_2;
            else 
            {
                if (b%2)
                    angle = -M_PI;
                else
                    angle = M_PI;
            }
            imageFrame = [[foldFrames objectAtIndex:(foldFrames.count - 1 - b)] CGRectValue];
        }
        
        CATransformLayer *transLayer = nil;
        if (isSpread)
        {
            transLayer = [self transformLayerFromImage:viewSnapShot Frame:imageFrame Direction:direction Duration:duration AnchorPiont:anchorPoint StartAngle:angle EndAngle:0];
        }
        else 
        {
            transLayer = [self transformLayerFromImage:viewSnapShot Frame:imageFrame Direction:direction Duration:duration AnchorPiont:anchorPoint StartAngle:0 EndAngle:angle];
        }
        
        [prevLayer addSublayer:transLayer];
        prevLayer = transLayer;
        transLayer = nil;
    }
    viewSnapShot = nil;
    if (completion)
    {
        //阻塞主线程
        CFRunLoopRun();
        completion(YES);
    }
}

/********************************************************************
 *动画效果结束时
 *******************************************************************/
- (void)animationDidStop:(CAAnimation *)anim finished:(BOOL)flag
{
    self->_finishedAnimation++;   
    
    if (self->_finishedAnimation == self->_totalAnimation)
    {
        if (!self->_isSpead)
        {
            self->_backgroundImage = nil;
        }
        
        for (int i=self->_joinLayers.count-1; i>=0; --i)
        {
            CATransformLayer *layer = [self->_joinLayers objectAtIndex:i];
            [layer removeAllAnimations];
            [layer removeFromSuperlayer];
            layer = nil;
        }
        
        for (int i=self->_imageLayers.count-1; i>=0; --i)
        {
            CALayer *layer = [self->_imageLayers objectAtIndex:i];
            [layer removeAllAnimations];
            [layer removeFromSuperlayer];
            layer = nil;
        }
        
        for (int i=self->_shadowLayers.count-1; i>=0; --i)
        {
            CAGradientLayer *layer = [self->_shadowLayers objectAtIndex:i];
            [layer removeAllAnimations];
            [layer removeFromSuperlayer];
            layer = nil;
        }
        
        [self->_origamiLayer removeAllAnimations];
        [self->_origamiLayer removeFromSuperlayer];        
        self->_origamiLayer = nil;
        
        [self->_joinLayers removeAllObjects];
        [self->_imageLayers removeAllObjects];
        [self->_shadowLayers removeAllObjects];
        
        if (self->currentLoop)
        {
            CFRunLoopStop(self->currentLoop);
        }
    }
}

/********************************************************************
 *设置动画效果过程中的背景试图
 *animationView:是需要动的view
 *******************************************************************/
-(void)backgroundImage:(UIView *)backView AnimaitonView:(UIView*)animationView
{
    self->_backgroundImage = [Origami imageFromView:backView Rect:animationView.frame];
}

+(float)screenScale 
{
    if ([ [UIScreen mainScreen] respondsToSelector: @selector(scale)] == YES) {
        return [ [UIScreen mainScreen] scale];
    }
    return 1;
}

+(UIImage *)imageFromView: (UIView *) view 
{
    CGFloat scale = [Origami screenScale];
    
    if (scale > 1) 
    {
        UIGraphicsBeginImageContextWithOptions(view.bounds.size, view.opaque, scale);
    } 
    else 
    {
        UIGraphicsBeginImageContext(view.bounds.size);
    }
    
    CGContextRef context = UIGraphicsGetCurrentContext();
    [view.layer renderInContext: context];
    UIImage *viewImage = UIGraphicsGetImageFromCurrentImageContext();
    UIGraphicsEndImageContext();
    return viewImage;
}

/********************************************************************
 *根据视图截取某个区域的图
 *******************************************************************/
+(UIImage *)imageFromView:(UIView *)view Rect:(CGRect)cropRC
{
    UIImage *viewImage = [Origami imageFromView:view];
    return [Origami cropImage:viewImage Rect:cropRC];
}

/********************************************************************
 *裁剪某个图形
 *******************************************************************/
+(UIImage *)cropImage:(UIImage *)srcImage Rect:(CGRect)cropRC
{
    cropRC.origin.x *= srcImage.scale;
    cropRC.origin.y *= srcImage.scale;
    cropRC.size.width *= srcImage.scale;
    cropRC.size.height *= srcImage.scale;
    
    CGImageRef imageCrop = CGImageCreateWithImageInRect(srcImage.CGImage, cropRC);
    UIImage *cropImage = [[UIImage alloc] initWithCGImage:imageCrop scale:srcImage.scale orientation:UIImageOrientationUp];
    CGImageRelease(imageCrop);
    return cropImage;
}

@end
