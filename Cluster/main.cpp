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



using namespace std;

int cluster()
{
system("rm -r ~/Documents/C++/Cluster/cluster");
/*************************************************************/
  //declaration
  time_t ID;
  ID = time (NULL);
  vector<string> url;//list of urls
  string request;
  string Parts;
  int count=0;
  string::iterator it;
  TiXmlDocument *doc=new TiXmlDocument("sample.xml");
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
//retrieve URL
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
              ofstream myfile ("test.php");
              if (myfile.is_open())
              {
                //TODO clean this by making a seperate php file and calling it with an argument for the url
                  myfile << "<?php\n";
                  myfile << "include 'htmlparser.php';\n";
                  myfile << "$html = file_get_html('"<<*iter<<"');\n";
                  myfile << "foreach($html->find('title') as $element);\n";
                  myfile << "$f = fopen('./txt/"<<count<<".txt', 'w');\n";
                  myfile << "fwrite($f, $element);\n";
                  myfile << "fwrite($f, $html->plaintext);\n\n\n";
                  myfile << "fclose($f);\n";
                  myfile << "?>";
                  myfile.close();
              }
              system("php test.php");
     }
/*************************************************************/



/*************************************************************/
//Build index
       ofstream myfile ("files.list");
       if (myfile.is_open())
        {
           for (int i=1;i<=count;i++){myfile <<"./txt/"<<i<<".txt\n";}
           myfile.close();
        }
       system("perl txttotrec.pl files.list");//need

       ofstream docu ("documents.list");
       if (docu.is_open()){
        docu <<"documents.txt\n";
       }
        docu.close();
        system("BuildIndex buildindex.param");//install lemur
/*************************************************************/
//create cluster parameter file& do cluster
  system("rm -r ~/Documents/C++/Cluster/cluster.param");
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
  //TODO change this to reflect my directory
  //system("~/Documents/C++/Cluster/OfflineCluster cluster.param > cluster.txt");
  system("~/Documents/lemur-4.12/cluster/src/OfflineCluster cluster.param > cluster.txt");
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

int main(){
     cluster();
}
