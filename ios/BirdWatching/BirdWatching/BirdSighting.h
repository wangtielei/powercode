//
//  BirdSighting.h
//  BirdWatching
//
//  Created by guanjianjun on 7/14/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface BirdSighting : NSObject

//定义三个属性
@property (nonatomic, copy) NSString *name;
@property (nonatomic, copy) NSString *location;
@property (nonatomic, strong) NSDate *date;

//定义方法
-(id)initWithName:(NSString *)name location:(NSString *)location date:(NSDate *)date;

@end
