
#ifndef __QR_ENCODER__
#define __QR_ENCODER__
#include "win2ansi.h"
//

/////////////////////////////////////////////////////////////////////////////

#define QR_LEVEL_L	0
#define QR_LEVEL_M	1
#define QR_LEVEL_Q	2
#define QR_LEVEL_H	3

// 
#define QR_MODE_NUMERAL		0
#define QR_MODE_ALPHABET	1
#define QR_MODE_8BIT		2
#define QR_MODE_KANJI		3

// 
#define QR_VRESION_S	0 // 
#define QR_VRESION_M	1 // 
#define QR_VRESION_L	2 // 

#define MAX_ALLCODEWORD	 3706 // 
#define MAX_DATACODEWORD 2956 // 
#define MAX_CODEBLOCK	  153 // 
#define MAX_MODULESIZE	  177 // 

// 
#define QR_MARGIN	4


/////////////////////////////////////////////////////////////////////////////
typedef struct tagRS_BLOCKINFO
{
	int ncRSBlock;		// 
	int ncAllCodeWord;	// 
	int ncDataCodeWord;	// 

} RS_BLOCKINFO, *LPRS_BLOCKINFO;


/////////////////////////////////////////////////////////////////////////////
// 

typedef struct tagQR_VERSIONINFO
{
	int nVersionNo;	   // 
	int ncAllCodeWord; // 

	//  
	int ncDataCodeWord[4];	

	int ncAlignPoint;	// 
	int nAlignPoint[6];	// 

	RS_BLOCKINFO RS_BlockInfo1[4]; // 
	RS_BLOCKINFO RS_BlockInfo2[4]; //

} QR_VERSIONINFO, *LPQR_VERSIONINFO;


/////////////////////////////////////////////////////////////////////////////
// CQR_Encode 

class CQR_Encode
{
// 
public:
	CQR_Encode();
	~CQR_Encode();

public:
	int m_nLevel;		// 
	int m_nVersion;		// 
	QRBOOL m_bAutoExtent;	//
	int m_nMaskingNo;	// 

public:
	int m_nSymbleSize;
	BYTE m_byModuleData[MAX_MODULESIZE][MAX_MODULESIZE]; // [x][y]
	// bit5:
	// bit4:
	// bit1:
	// bit0:
	// 20h

private:
	int m_ncDataCodeWordBit; // 
	BYTE m_byDataCodeWord[MAX_DATACODEWORD]; // 

	int m_ncDataBlock;
	BYTE m_byBlockMode[MAX_DATACODEWORD];
	int m_nBlockLength[MAX_DATACODEWORD];

	int m_ncAllCodeWord; //
	BYTE m_byAllCodeWord[MAX_ALLCODEWORD]; // 
	BYTE m_byRSWork[MAX_CODEBLOCK];	//

// 
public:
	QRBOOL EncodeData(int nLevel, int nVersion, QRBOOL bAutoExtent, int nMaskingNo, LPCSTR lpsSource, int ncSource = 0);

private:
	int GetEncodeVersion(int nVersion, LPCSTR lpsSource, int ncLength);
	QRBOOL EncodeSourceData(LPCSTR lpsSource, int ncLength, int nVerGroup);

	int GetBitLength(BYTE nMode, int ncData, int nVerGroup);

	int SetBitStream(int nIndex, WORD wData, int ncData);

	QRBOOL IsNumeralData(unsigned char c);
	QRBOOL IsAlphabetData(unsigned char c);
	QRBOOL IsKanjiData(unsigned char c1, unsigned char c2);

	BYTE AlphabetToBinaly(unsigned char c);
	WORD KanjiToBinaly(WORD wc);

	void GetRSCodeWord(LPBYTE lpbyRSWork, int ncDataCodeWord, int ncRSCodeWord);

// 
private:
	void FormatModule();

	void SetFunctionModule();
	void SetFinderPattern(int x, int y);
	void SetAlignmentPattern(int x, int y);
	void SetVersionPattern();
	void SetCodeWordPattern();
	void SetMaskingPattern(int nPatternNo);
	void SetFormatInfoPattern(int nPatternNo);
	int CountPenalty();
};

/////////////////////////////////////////////////////////////////////////////

#endif // !defined(AFX_QR_ENCODE_H__AC886DF7_C0AE_4C9F_AC7A_FCDA8CB1DD37__INCLUDED_)
