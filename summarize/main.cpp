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


int summarization(string bin)
{
/*************************************************************/
	vector<string> url;
    string request;
	string maxSents;
	string ind_sum;
	int count=0;
  string cmd;
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
  //remove old index
  cmd = "rm -r summarizationIndex";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
  cmd = bin + "BuildIndex buildindex.param";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
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
    myfile << "<index>summarizationIndex</index>\n";
    myfile << "<summLength>"<<maxSents<<"</summLength>\n";
    myfile << "<docID>"<<i<<"</docID>\n";
    myfile << "</parameters>\n";
    myfile.close();
  }
  else cout << "Unable to open file";
  cmd = bin + "BasicSummApp sum.param > sum.txt";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
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
void help(char *err){
  if(err != 0){
    cout << err;
  }
  cout << "Arguments: \n";
  cout << "-i <input file>\t\t input xml file\n-o <output directory>\t directory where to put output (default is 'output')\n";
}
int main(int argc, const char **argv){
  string input_file;
  bool input_found = false;
  string bin_directory = "../bin/"; //where BasicSumApp is located
  for(int i = 1; i < argc; i++){
    if(strcmp(argv[i],"-i") == 0){
      //next argument is input file
      if(i < argc-1){
        i++;
        input_file = argv[i];
        input_found = true;
      }
      else{
        help((char *)"Invalid arguments\n");
        return 1;
      }
    }
  }

  //TODO check directories for leading slash
  if(!input_found){
      help((char *)"Invalid arguments\n");
      return 1;
  }
  summarization(bin_directory);
}

