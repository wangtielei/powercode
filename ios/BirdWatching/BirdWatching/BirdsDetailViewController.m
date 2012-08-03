//
//  BirdsDetailViewController.m
//  BirdWatching
//
//  Created by guanjianjun on 7/14/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import "BirdsDetailViewController.h"
#import "BirdSighting.h"

@interface BirdsDetailViewController ()
- (void)configureView;
@end

@implementation BirdsDetailViewController

@synthesize sighting = _sighting;
@synthesize birdNameLabel = _birdNameLabel;
@synthesize locationLabel = _locationLabel;
@synthesize dateLabel = _dateLabel;

#pragma mark - Managing the detail item

- (void)setSighting:(BirdSighting *) newSighting
{    
    if (_sighting != newSighting) 
    {        
        _sighting = newSighting;
        // Update the view.
        [self configureView];        
    }    
}

- (void)configureView
{
    // Update the user interface for the detail item.
    BirdSighting *theSighting = self.sighting;
    static NSDateFormatter *formatter = nil;    
    if (formatter == nil) 
    {        
        formatter = [[NSDateFormatter alloc] init];        
        [formatter setDateStyle:NSDateFormatterMediumStyle];        
    }
    
    if (theSighting) 
    {        
        self.birdNameLabel.text = theSighting.name;        
        self.locationLabel.text = theSighting.location;        
        self.dateLabel.text = [formatter stringFromDate:(NSDate *)theSighting.date];        
    }
}

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
    [self configureView];
}

- (void)viewDidUnload
{
    self.sighting = nil;
    [super viewDidUnload];
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    return (interfaceOrientation != UIInterfaceOrientationPortraitUpsideDown);
}

@end
