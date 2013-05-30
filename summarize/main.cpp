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

void clean(){
  //Remove temporary files
    string cmd;
    cout << "Cleaning up \n";
    cmd = "rm -r summarizationIndex txt/* sum.param";
    cout << cmd << "\n";
    system((const char *)cmd.c_str());

}
int summarization(string in)
{

  clean();

/*************************************************************/
	vector<string> url;
    string request;
	string maxSents;
	string ind_sum;
	int count=0;
  string cmd;
/*************************************************************/

/*************************************************************/
    TiXmlDocument *doc=new TiXmlDocument((const char *)in.c_str());
	bool loadedOkay = doc->LoadFile();
  if(!loadedOkay){
    cout << "ERROR: xml document invalid\n";
    return 1;
  }
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
      char tmp_cmd[50 + (*iter).length()];
      sprintf(tmp_cmd, "php -f ../lib/fetch_html_as_text.php doc_%d %s ../summarize/",count, (*iter).c_str());
      cmd = tmp_cmd;
      cout << cmd << "\n";
      system((const char *)cmd.c_str());
    }
    cout << "Fetched html documents are now in txt/ directory in plaintext with title element\n";
/*************************************************************/

/*************************************************************/
        ofstream myfile ("files.list");
       if (myfile.is_open())
        {
           for (int i=1;i<=count;i++){myfile <<"./txt/"<<i<<".txt\n";}
           myfile.close();
        }
       system("perl ../lib/txttotrec.pl files.list ../summarize");

        ofstream docu ("documents.list");
       if (docu.is_open())
        {
         docu <<"documents.txt\n";
        }
        docu.close();

  system((const char *)cmd.c_str());
  cmd = "../bin/BuildIndex buildindex.param";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
/*************************************************************/

/*************************************************************/
ofstream summ ("output.xml");
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
  cmd = "../bin/BasicSummApp sum.param > sum.txt";
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
  //clean();
  cout << "Output is in output.xml\n";
  return 0;
}
void help(char *err){
  if(err != 0){
    cout << err;
  }
  cout << "Arguments: \n";
  cout << "-i <input file>\t\t input xml file\n";
}
int main(int argc, const char **argv){
  string input_file;
  bool input_found = false;
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
  return summarization(input_file);
}

