#include <stdlib.h>
#include "tinystr.h"
#include "tinyxml.h"
#include "iostream"
#include <sstream>
#include <stdio.h>
#include <fstream>
#include <vector>
#include "string"
using namespace std;


int summarization()
{
/*************************************************************/
	vector<string> url;
    string request;
	string maxSents;
	string ind_sum;
	int count=0;
/*************************************************************/

/*************************************************************/
    TiXmlDocument *doc=new TiXmlDocument("sample.xml");
	doc->LoadFile();
    TiXmlElement *root = doc->RootElement();
	TiXmlElement *ele= root->FirstChildElement();
	request=ele->GetText();
	ele=ele->NextSiblingElement();
	maxSents=ele->GetText();
    ele=ele->NextSiblingElement();
	ind_sum=ele->GetText();
	ele=ele->NextSiblingElement();
	ele=ele->FirstChildElement();
    
	while(ele)
	{
     TiXmlElement *temp=ele->FirstChildElement();
     url.push_back(temp->GetText());
	 ele=ele->NextSiblingElement();
	}
/*************************************************************/
	 for (vector<string>::iterator iter=url.begin(); iter != url.end();iter++)
    {
		      count++;
			  stringstream ss;
			  string line;
              ofstream myfile ("test.php");
              if (myfile.is_open())
              {
                  myfile << "<?php\n";
                  myfile << "include 'htmlparser.php';\n";
                  myfile << "$html = file_get_html('"<<*iter<<"');\n";
                  myfile << "foreach($html->find('title') as $element);\n";
                  myfile << "$f = fopen('./txt/"<<count<<".txt', 'w');\n";
                  myfile << "fwrite($f, $html->plaintext);\n";
                  myfile << "fwrite($f, $element);\n";
                  myfile << "fclose($f);\n";
                  myfile << "?>";
                  myfile.close();
              }
              system("php test.php");
     }
/*************************************************************/

/*************************************************************/
        ofstream myfile ("files.list");
       if (myfile.is_open())
        {
           for (int i=1;i<=count;i++){myfile <<"./txt/"<<i<<".txt\n";}
           myfile.close();
        }
       system("perl txttotrec.pl files.list");

        ofstream docu ("documents.list");
       if (docu.is_open())
        {
         docu <<"documents.txt\n";
        }
        docu.close();
   system("BuildIndex buildindex.param");
/*************************************************************/

/*************************************************************/
ofstream summ ("summarization.xml");
if (summ.is_open()){
    summ << "<parameters>\n";
    summ << "<requestType>summarize</requestType>\n";
    summ << "<docList>\n";

for (int i=1;i<=count;i++){
  ofstream myfile ("sum.param");
  if (myfile.is_open())
  {
    myfile << "<parameters>\n";
    myfile << "<index>/home/think_different/Documents/C++/summ/summarization</index>\n";
    myfile << "<summLength>"<<maxSents<<"</summLength>\n";
    myfile << "<docID>"<<i<<"</docID>\n";
    myfile << "</parameters>\n";
    myfile.close();
  }
  else cout << "Unable to open file";
  system("/home/think_different/Documents/C++/summ/BasicSummApp sum.param > sum.txt");
    summ <<"<doc>\n";
    summ <<"<docID>"<<i<<"</docID>\n";
    summ <<"<summarization>\n";
    ifstream infile;
    infile.open ("sum.txt");
    string STRING;
    while(getline(infile,STRING)) // To get you all the lines.
        {
	        summ << STRING<<"\n"; // Prints our STRING.
        }
     summ <<"</summarization>\n";
    summ <<"</doc>\n";

    }
  summ << "</docList>\n";
  summ << "</parameters>\n";

  }
  summ.close();
}

main(){
    summarization();
}

