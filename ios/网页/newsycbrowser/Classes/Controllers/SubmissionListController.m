//
//  SubmissionListController.m
//  newsyc
//
//  Created by Grant Paul on 2/24/12.
//  Copyright (c) 2012 Xuzz Productions, LLC. All rights reserved.
//

#import "SubmissionListController.h"

#import "SubmissionTableCell.h"
#import "CommentListController.h"

#import "AppDelegate.h"

@implementation SubmissionListController

+ (Class)cellClass {
    return [SubmissionTableCell class];
}

- (CGFloat)cellHeightForEntry:(HNEntry *)entry {
    return [SubmissionTableCell heightForEntry:entry withWidth:[[self view] bounds].size.width];
}

- (void)configureCell:(UITableViewCell *)cell forEntry:(HNEntry *)entry {
    SubmissionTableCell *cell_ = (SubmissionTableCell *) cell;
    [cell_ setSubmission:entry];
}

- (void)cellSelected:(UITableViewCell *)cell forEntry:(HNEntry *)entry {
    CommentListController *controller = [[CommentListController alloc] initWithSource:entry];
    [[self navigationController] pushController:[controller autorelease] animated:YES];
}

- (void)deselectWithAnimation:(BOOL)animated {
    if ([[UIDevice currentDevice] userInterfaceIdiom] != UIUserInterfaceIdiomPad) {
        [super deselectWithAnimation:animated];
    }
}

AUTOROTATION_FOR_PAD_ONLY

@end
