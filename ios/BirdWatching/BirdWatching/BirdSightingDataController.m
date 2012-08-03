//
//  BirdSightingDataController.m
//  BirdWatching
//
//  Created by guanjianjun on 7/14/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import "BirdSightingDataController.h"
#import "BirdSighting.h"

/**
 * 这是类扩展，类扩展允许定义私有函数
 */
@interface BirdSightingDataController ()

- (void)initializeDefaultDataList;

@end


/**
 * 类的实现函数
 */
@implementation BirdSightingDataController

@synthesize masterBirdSightingList = _masterBirdSightingList;

- (id)init 
{    
    if (self = [super init]) 
    {        
        [self initializeDefaultDataList];        
        return self;        
    }    
    return nil;    
}

- (void)initializeDefaultDataList 
{    
    NSMutableArray *sightingList = [[NSMutableArray alloc] init];    
    self.masterBirdSightingList = sightingList;    
    [self addBirdSightingWithName:@"Pigeon" location:@"Everywhere"];    
}

- (void)setMasterBirdSightingList:(NSMutableArray *)newList 
{    
    if (_masterBirdSightingList != newList) 
    {        
        _masterBirdSightingList = [newList mutableCopy];
    }    
}

- (NSUInteger)countOfList 
{    
    return [self.masterBirdSightingList count];    
}

- (BirdSighting *)objectInListAtIndex:(NSUInteger)theIndex 
{    
    return [self.masterBirdSightingList objectAtIndex:theIndex];    
}

- (void)addBirdSightingWithName:(NSString *)inputBirdName location:(NSString *)inputLocation 
{    
    BirdSighting *sighting;    
    NSDate *today = [NSDate date];    
    sighting = [[BirdSighting alloc] initWithName:inputBirdName location:inputLocation date:today];    
    [self.masterBirdSightingList addObject:sighting];    
}

@end
