#include <stdlib.h>
#include "tinystr.h"
#include "tinyxml.h"
#include "iostream"
#include <sstream>
#include <stdio.h>
#include <fstream>
#include <vector>
#include "string"
#include <time.h>
#include <string.h>


using namespace std;

void clean(){
  //Remove temporary files
    string cmd;
    cout << "Cleaning up \n";
    cmd = "rm -r cluster.param clusterIndex files.list txt/*";
    cout << cmd << "\n";
    system((const char *)cmd.c_str());

}
int cluster(string in)
{
/*************************************************************/
  //declaration
  time_t ID;
  string cmd; //system command string
  ID = time (NULL);
  vector<string> url;//list of urls
  string request;
  string Parts;
  int count=0;
  string::iterator it;
  TiXmlDocument *doc=new TiXmlDocument((const char *)in.c_str());
  bool loadOkay = doc->LoadFile();
  if(!loadOkay){
    cout << "ERROR: xml document not valid\n";
    return 1;
  }
  TiXmlElement *root = doc->RootElement();
  TiXmlElement *ele= root->FirstChildElement();
  request=ele->GetText();
  ele=ele->NextSiblingElement();
  Parts=ele->GetText();
  ele=ele->NextSiblingElement();
  ele=ele->FirstChildElement();
  int Num_Cluster = atoi(Parts.c_str());
/*************************************************************/

clean();
/*************************************************************/
//retrieve URL (eventually this may be document id's)
	while(ele)
	{
    TiXmlElement *temp=ele->FirstChildElement();
    url.push_back(temp->GetText());
    ele=ele->NextSiblingElement();
	}
//retrieve HTML
 for (vector<string>::iterator iter=url.begin(); iter != url.end();iter++)
  {
	    count++;
      char tmp_cmd[50 + (*iter).length()];
      sprintf(tmp_cmd, "php -f ../lib/fetch_html_as_text.php doc_%d %s ../cluster/",count, (*iter).c_str());
      cmd = tmp_cmd;
      cout << cmd << "\n";
      system((const char *)cmd.c_str());
   }
   cout << "Fetched html documents are now in txt/ directory in plaintext with title element\n";
/*************************************************************/



/*************************************************************/
//Build index
  ofstream myfile ("files.list");
  if (myfile.is_open())
  {
     for (int i=1;i<=count;i++){myfile << "txt/doc_"<<i<<".txt\n";}
     myfile.close();
  }
  system("perl ../lib/txttotrec.pl files.list ../cluster/");//convert to trec filetype
  cout << "documents.txt now has all of the documents in TREC format\n";
  string document = string("documents.list");
  ofstream docu (document.c_str());
  if (docu.is_open()){
  docu << "documents.txt\n";
  }
  docu.close();
  cmd = "../bin/BuildIndex buildindex.param";
  system((const char *)cmd.c_str());
  cout << "Index generated\n";
/*************************************************************/
//create cluster parameter file& do cluster
  ofstream clusterparam ("cluster.param");
  if (clusterparam.is_open())
  {
    clusterparam << "<parameters>\n";
    clusterparam << "<index>clusterIndex</index>\n";
    clusterparam << "<docMode>max</docMode>\n";
    clusterparam << "<numParts>"<<Num_Cluster<<"</numParts>\n";
    clusterparam << "</parameters>\n";
    clusterparam.close();
  }

  //system("~/Documents/C++/Cluster/OfflineCluster cluster.param > cluster.txt");
  cmd = "../bin/OfflineCluster cluster.param > cluster.txt";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
    //need edit this place where cluster function lies
/*************************************************************/

/*************************************************************/
//write XML output file
        vector<string>::iterator iter=url.begin();
        document = string("output.xml");
        ofstream clu (document.c_str());
        clu<< "<parameters>\n";
        clu<<"<requestID>"<<ID<<"</requestID>\n";
        clu<<"<requestType>cluster</requestType>\n";

        clu<< "<clusterList>\n";
        ifstream results;
        document = string("cluster.txt");
        results.open (document.c_str());
        string STRING;
        int c=0;
        while(getline(results,STRING)) // To get you all the lines.
        {
        c++;//lol
        if(c<=(Num_Cluster+1)&&c!=1){
        it=STRING.begin()+6;
        clu<<"<clusterID>"<<c-1<<"</clusterID>\n"<<"<docList>\n";
        for(it;it<STRING.end();it=it+2) {
            clu<<"<doc>\n"<<"<docID>"<<*it<<"</docID>\n";
            clu<<"<url>"<<*iter<<"</url>\n"<<"</doc>\n";
            iter++;
            }
        clu<<"</docList>\n";
        }

        }
        clu<< "</clusterList>\n";
        clu<< "</parameters>";
        clu.close();

/************************************************************/
        clean();
        cout << "Output is in output.xml\n";

        cout << "SUCCESS\n";
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
  //get arguments for directory
  string input_file;
  bool input_found = false;
  bool isVerbose = false;

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
  if(!input_found){
      help((char *)"Invalid arguments\n");
      return 1;
  }

  return cluster(input_file);
}
