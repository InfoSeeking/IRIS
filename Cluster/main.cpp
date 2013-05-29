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

int cluster(string in, string bin, string od)
{
  cout << "Removing old data\n";
  string cmd = "rm -r " + od + "cluster";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
/*************************************************************/
  //declaration
  time_t ID;
  ID = time (NULL);
  vector<string> url;//list of urls
  string request;
  string Parts;
  int count=0;
  string::iterator it;
  TiXmlDocument *doc=new TiXmlDocument((const char *)in.c_str());
  doc->LoadFile();
  TiXmlElement *root = doc->RootElement();
  TiXmlElement *ele= root->FirstChildElement();
  request=ele->GetText();
  ele=ele->NextSiblingElement();
  Parts=ele->GetText();
  ele=ele->NextSiblingElement();
  ele=ele->FirstChildElement();
  int Num_Cluster = atoi(Parts.c_str());
/*************************************************************/

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
		  stringstream ss;
		  string line;
      char tmp_cmd[50];
      sprintf(tmp_cmd, "php -f fetch_html_as_text.php doc_%d ",count);
      cmd =  tmp_cmd + (*iter);
      cout << cmd << "\n";
      system((const char *)cmd.c_str());
   }
   cout << "Fetched html documents are now in txt directory\n";
/*************************************************************/



/*************************************************************/
//Build index
       ofstream myfile ("files.list");
       if (myfile.is_open())
        {
           for (int i=1;i<=count;i++){myfile <<"./txt/doc_"<<i<<".txt\n";}
           myfile.close();
        }
       system("perl txttotrec.pl files.list");//convert to trec filetype

       ofstream docu ("documents.list");
       if (docu.is_open()){
        docu <<"documents.txt\n";
       }
        docu.close();
        cmd = bin + "BuildIndex buildindex.param";
        system((const char *)cmd.c_str());//install lemur
/*************************************************************/
//create cluster parameter file& do cluster
  cmd = "rm -r " + od + "cluster.param";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
  ofstream clusterparam ("cluster.param");
  if (clusterparam.is_open())
  {
    clusterparam << "<parameters>\n";
    clusterparam << "<index>cluster</index>\n";
    clusterparam << "<docMode>max</docMode>\n";
    clusterparam << "<numParts>"<<Num_Cluster<<"</numParts>\n";
    clusterparam << "</parameters>\n";
    clusterparam.close();
  }

  //system("~/Documents/C++/Cluster/OfflineCluster cluster.param > cluster.txt");
  cmd = bin + "OfflineCluster cluster.param > cluster.txt";
  cout << cmd << "\n";
  system((const char *)cmd.c_str());
    //need edit this place where cluster function lies
/*************************************************************/

/*************************************************************/
//write XML output file
        vector<string>::iterator iter=url.begin();
        ofstream clu ("cluster.xml");
        clu<< "<parameters>\n";
        clu<<"<requestID>"<<ID<<"</requestID>\n";
        clu<<"<requestType>cluster</requestType>\n";

        clu<< "<clusterList>\n";
        ifstream results;
        results.open ("cluster.txt");
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
     return 0;
}

void help(char *err){
  if(err != 0){
    cout << err;
  }
  cout << "Arguments: \n-o <output directory>\t directory where cluster data is stored\n";
  cout << "-i <input file>\t input xml file\n-b <bin directory>\t directory where OfflineCluster and other executables are located\n";
}

int main(int argc, const char **argv){
  //get arguments for directory
  string input_file;
  string bin_directory;
  string output_directory;

  if(argc != 7){
  help("Invalid arguments\n");
    return 1;
  }
  else{
    for(int i = 1; i < argc; i++){
      if(strcmp(argv[i],"-o") == 0){
        //next argument is output directory
        if(i < argc-1){
          i++;
          output_directory = argv[i];
        }
        else{
          help("Invalid arguments\n");
        }
      }
      else if(strcmp(argv[i],"-b") == 0){
        //next argument is bin directory
        if(i < argc-1){
          i++;
          bin_directory = argv[i];
        }
        else{
          help("Invalid arguments\n");
        }
      }
      else if(strcmp(argv[i],"-i") == 0){
        //next argument is input file
        if(i < argc-1){
          i++;
          input_file = argv[i];
        }
        else{
          help("Invalid arguments\n");
        }
      }
    }
  }

  cluster(input_file, bin_directory, output_directory);
  return 0;
}
