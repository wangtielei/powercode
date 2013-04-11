#############################################################
# 通用的C/C++工程makefile
# [用法]
# 1. 拷贝Makefile到你的工程根目录;
# 2. 根据需要改变[定制部分]的变量;
# 3. 如果要用到非标准的C/C++库,请设置INCPATH,LIBPATH和LDFLAGS;
# 4. 将HEADERDIRS改为本工程的头文件所在目录;
# 5. 将SRCDIRS改为本工程的源文件所在目录;
# 3. 执行命令: make 进行编译
#
# [make参数]
# ------------
# The Makefile provides the following targets to make:
# $ make: compile and link
# $ make NODEP=yes: compile and link without generating dependencies
# $ make objs: compile only (no linking)
# $ make tags: create tags for Emacs editor
# $ make ctags: create ctags for VI editor
# $ make clean: clean objects and the executable file
# $ make distclean: clean objects, the executable and dependencies
# $ make help: get the usage of the makefile
#
#============================================================
#========================参数帮助==============================
# 该模板建议的工程目录结构如下：
# project
# |--Makefile
# |--include
#    |--*.h
# |--src
#    |--*.cpp
# |--output
#    |--bin
#    |--lib
#
# **output是编译时创建的，会把生成的目标文件放到output/bin目录，同时将所有的.o文件
# 打包为一个静态库(.a)文件，放到output/lib下

# 赋值符可以使用’=‘，’:='，'?=', 追加用 '+='
# -O2 :表示优化选项，2表示最优优化
# -fpic :编译成位置无关代码,必须  不然你的程序在别的地方肯可能运行不了
# -shared  :当然是说要编译成共享库了
# -I :指定头文件的路径
# -L :指定动态库路径
# -l :指定需要链接的动态库名称(不包含'lib'和'.so'的部分)
# -D :定义宏，在C++代码里可以利用 #ifdef做检查
# -Wall :输出所有的警告信息
# -Werror :将警告视为错误
# 打印信息用 @echo，且需要以Tab开头

## [定制部分]:你可以根据自己的需求进行调整
##============================================================
# 工作根目录(一般只公共的库目录根路径)
WORKROOT := ~/code

# 最终目标名称(即可执行文件名或库名称)
PROGRAM := test

# [PROJECT]项目名称,有可能在代码中用FLAG_PROJECT宏来特殊处理
PROJECT := MakefileTest

# [VERSION]版本号,有可能在代码中用FLAG_VERSION宏来特殊处理
VERSION := 1.0.0

# [HEADERDIRS]头文件所在目录,如果不设置,则后面会使用当前目录
HEADERDIRS := include
# 如果不设置头文件目录,则使用当前目录
ifeq ($(HEADERDIRS),)
HEADERDIRS := .
endif


# [SRCDIRS]源文件所在目录,目前仅支持一个目录
SRCDIRS := src
# 如果不设置源文件目录,则使用当前目录
ifeq ($(SRCDIRS),)
SRCDIRS := .
endif

# [INCPATH]依赖的非本工程的头文件路径
INCPATH := -I$(HEADERDIRS) \
		   -I$(SRCDIRS) \
           -I/usr/include/ \
           -I/usr/local/include
#           -I$(WORKROOT)/other/include/

# [linker options]*.so文件所在路径
LIBPATH := -L/usr/lib \
          -L/usr/local/lib
#		   -L$(WORKROOT)/other/lib

# [link file]需要链接的文件名
LDFLAGS := -lpthread

# [CXXFLAGS]编译参数,如果不需要debug可以去掉'-g'选项
CXXFLAGS := -DFLAG_PROJECT="\"$(PROJECT)\""\
            -DFLAG_VERSION="\"$(VERSION)\""\
            -g
# 如果目标文件是library(即*.so)则去掉下面行的注释
#CXXFLAGS += -shared

##===========handle 64 or 32==============
os_type = $(shell getconf LONG_BIT)
ifeq ($(os_type), 64)
else
endif
##========================================


## 以下是固定部分,不要轻易改变他们
##========================================

# The pre-processor options used by the cpp (man cpp for more).
CPPFLAGS := -O2 -fpic -W -Wall -Winline -Wno-unused-parameter -Wno-unused-function

# The source file types (headers excluded).
# .c indicates C source files, and others C++ ones.
SRCEXTS := .c .C .cc .cpp .CPP .c++ .cxx .cp

# The header file types.
HDREXTS := .h .H .hh .hpp .HPP .h++ .hxx .hp

# The pre-processor and compiler options.
# Users can override those variables from the command line.
CFLAGS := $(CPPFLAGS)
# CXXFLAGS= -O2

# The C program compiler.
CC = gcc

# The C++ program compiler.
CXX = g++

# Un-comment the following line to compile C programs as C++ ones.
#CC = $(CXX)

# The command used to delete file.
RM = rm -f

ETAGS = etags
ETAGSFLAGS =

CTAGS = ctags
CTAGSFLAGS =

## Stable Section: usually no need to be changed. But you can add more.
##==========================================================================
SHELL = /bin/sh
EMPTY =
SPACE = $(EMPTY) $(EMPTY)
ifeq ($(PROGRAM),)
CUR_PATH_NAMES = $(subst /,$(SPACE),$(subst $(SPACE),_,$(CURDIR)))
PROGRAM = $(word $(words $(CUR_PATH_NAMES)),$(CUR_PATH_NAMES))
ifeq ($(PROGRAM),)
PROGRAM = a.out
endif
endif
ifeq ($(SRCDIRS),)
SRCDIRS = .
endif
SOURCES = $(foreach d,$(SRCDIRS),$(wildcard $(addprefix $(d)/*,$(SRCEXTS))))
HEADERS = $(foreach d,$(HEADERDIRS),$(wildcard $(addprefix $(d)/*,$(HDREXTS))))
SRC_CXX = $(filter-out %.c,$(SOURCES))
OBJS = $(addsuffix .o, $(basename $(SOURCES)))
DEPS = $(OBJS:.o=.d)

## Define some useful variables.
DEP_OPT = $(shell if `$(CC) --version | grep "GCC" >/dev/null`; then \
echo "-MM -MP"; else echo "-M"; fi )
DEPEND = $(CC) $(DEP_OPT) $(CXXFLAGS) $(CPPFLAGS)
DEPEND.d = $(subst -g ,,$(DEPEND))
COMPILE.c = $(CC) $(CXXFLAGS) $(CFLAGS) $(INCPATH) -c
COMPILE.cxx = $(CXX) $(CXXFLAGS) $(CPPFLAGS) $(INCPATH) -c
LINK.c = $(CC) $(CXXFLAGS) $(CFLAGS) $(LIBPATH) $(LDFLAGS)
LINK.cxx = $(CXX) $(CXXFLAGS) $(CPPFLAGS) $(LIBPATH) $(LDFLAGS)

.PHONY: all objs tags ctags clean distclean help show

# Delete the default suffixes
.SUFFIXES:

all: $(PROGRAM) output

# Rules for creating dependency files (.d).
#------------------------------------------

%.d:%.c
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

%.d:%.C
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

%.d:%.cc
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

%.d:%.cpp
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

%.d:%.CPP
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

%.d:%.c++
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

%.d:%.cp
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

%.d:%.cxx
#	@echo -n $(dir $<) > $@
#	@$(DEPEND.d) $< >> $@

# Rules for generating object files (.o).
#----------------------------------------
objs:$(OBJS)

%.o:%.c
	$(COMPILE.c) $< -o $@

%.o:%.C
	$(COMPILE.cxx) $< -o $@

%.o:%.cc
	$(COMPILE.cxx) $< -o $@

%.o:%.cpp
	$(COMPILE.cxx) $< -o $@

%.o:%.CPP
	$(COMPILE.cxx) $< -o $@

%.o:%.c++
	$(COMPILE.cxx) $< -o $@

%.o:%.cp
	$(COMPILE.cxx) $< -o $@

%.o:%.cxx
	$(COMPILE.cxx) $< -o $@

# Rules for generating the tags.
#-------------------------------------
tags: $(HEADERS) $(SOURCES)
	$(ETAGS) $(ETAGSFLAGS) $(HEADERS) $(SOURCES)

ctags: $(HEADERS) $(SOURCES)
	$(CTAGS) $(CTAGSFLAGS) $(HEADERS) $(SOURCES)

# Rules for generating the executable.
#-------------------------------------
$(PROGRAM):$(OBJS)
ifeq ($(SRC_CXX),) # C program
	$(LINK.c) $(OBJS) -o $@
	@echo Type ./$@ to execute the program.
else # C++ program
	$(LINK.cxx) $(OBJS) -o $@
	@echo Type ./$@ to execute the program.
endif

ifndef NODEP
ifneq ($(DEPS),)
sinclude $(DEPS)
endif
endif

output:
	`if [ -e "output" ]; then rm -rf output ;fi`
	mkdir -p output
	mkdir -p output/lib
	ar r ./output/lib/$(PROGRAM).a $(SRCDIRS)/*.o
	mkdir -p output/bin
	cp -rf $(PROGRAM) output/bin
	`if [ -e "conf" ];then cp -rf conf output/bin ;fi`

clean:
	$(RM) $(OBJS) $(PROGRAM) $(PROGRAM).exe
	rm -rf output

distclean: clean
	$(RM) $(DEPS) TAGS

# Show help.
help:
	@echo
	@echo 'Usage: make [TARGET]'
	@echo 'TARGETS:'
	@echo ' all: (=make) compile and link.'
	@echo ' NODEP=yes: make without generating dependencies.'
	@echo ' objs: compile only (no linking).'
	@echo ' tags: create tags for Emacs editor.'
	@echo ' ctags: create ctags for VI editor.'
	@echo ' clean: clean objects and the executable file.'
	@echo ' distclean: clean objects, the executable and dependencies.'
	@echo ' show: display variables (for debug use only).'
	@echo ' help: print this message.'
	@echo

# Show variables (for debug use only.)
show:
	@echo 'WORKROOT :' $(WORKROOT)
	@echo 'PROGRAM :' $(PROGRAM)
	@echo 'VERSION :' $(VERSION)
	@echo 'CXXFLAGS :' $(CXXFLAGS)
	@echo 'HEADERDIRS :' $(HEADERDIRS)
	@echo 'SRCDIRS :' $(SRCDIRS)
	@echo 'INCPATH :' $(INCPATH)
	@echo 'LDFLAGS :' $(LDFLAGS)
	@echo 'HEADERS :' $(HEADERS)
	@echo 'SOURCES :' $(SOURCES)
	@echo 'SRC_CXX :' $(SRC_CXX)
	@echo 'OBJS :' $(OBJS)
	@echo 'DEPS :' $(DEPS)
	@echo 'DEPEND :' $(DEPEND)
	@echo 'COMPILE.c :' $(COMPILE.c)
	@echo 'COMPILE.cxx :' $(COMPILE.cxx)
	@echo 'link.c :' $(LINK.c)
	@echo 'link.cxx :' $(LINK.cxx)

## End of the Makefile ## Suggestions are welcome ## All rights reserved ##
##############################################################
