//
//  BirdSighting.m
//  BirdWatching
//
//  Created by guanjianjun on 7/14/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import "BirdSighting.h"

@implementation BirdSighting

@synthesize name = _name;
@synthesize location = _location;
@synthesize date = _date;

- (id)initWithName:(NSString *)name location:(NSString *)location date:(NSDate *)date
{    
    self = [super init];    
    if (self) 
    {        
        _name = name;        
        _location = location;        
        _date = date;        
        return self;        
    }
    return nil;
}

@end
