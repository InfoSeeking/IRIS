#Functionality
Here is a list of all of the current controllers:
- [merge](#merge) - merge multiple resourceList elements into one
- [pipe](#pipe) - do a Unix like pipe by using the output of a request as input to the next
- [limit](#limit) - SQL style limit of documents returned
- [sort](#sort) - sort documents on a supplied field (deprecated)
- [extract](#extract) - extract keywords by frequency
- [filter](#filter) - remove words from document content
- [query](#query) - do a boolean type query on a group of documents
- [rank](#rank) - rank documents based on frequency of words supplied
- [vector_rank](#vector-rank) - rank documents on a vector model
- [index_insert](#index-insert) - create or add to index
- [index_query](#index-query) - do an Indri-like query on an index
- [index_delete](#index-delete) - delete an index
- [fetch](#fetch) - fetch document content
- [extract_blocks](#extract-blocks) - search for a block of text
- [summarize_sentences](#summarize-sentences) - gives the most important sentences as a summarization
- [if_then](#if-then) - perform an if-then form of control flow
- [halt](#halt) - stop execution of a pipe

#Directory Structure
- bin/ - Executable files (compiled on Linux Mint)
- extension/ - written in C to handle text processing, compiled version is called text_processing and is in bin folder
- controllers/ - files which perform specific tasks (e.g. clustering, summarization, etc.)
- library/ - helpful methods shared among all controllers
- storage/ - folder where all of the request/response data is stored, where all of the necessary indexing files are created (and removed)
- tests/ - this folder has all of our testing XML files, most of which are very simple and used for debugging, however, under tests/higher_behavior, there are more interesting XML tests using piping to do more complex actions
- config.php - configuration for debugging and file paths
- dbconfig.php.example - configuration file for database (remove the trailing .example to use)

#Issues and Disclaimers:
- There is no user authentication implemented. As of now each request MUST HAVE the clientID element which I plan to replace with OAuth.
- As the toolkit is now, persistent documents are never deleted. This may change in the future if I make a tool to delete old and unused cached pages/indexes but at the moment it 

#How to Alter and Host IRIS
##Server Prerequisites and Considerations
To run IRIS, you need the following:
- [MiniXML](http://www.msweet.org/projects.php?Z3) for C extensions (not required if you don't need to recompile the text_processing binaries)
- PHP 5.4.6 (It probably works on other versions, but this is what is was developed on)
- MySQL (for storing request information, however, this can be removed if necessary)
- Permissions to write to storage folder

Some considerations:
- You should have roughly 200MB of storage space minimum. This covers approximately 1000 requests, responses, and pages as well as 100 indexes. This is a very rough estimate and if you intend on storing persistent requests as well as indexes more space would be advisable.
- You may want to increase the PHP maximum execution time. If you are expecting requests with a lot of URLs (not content) fetching every document may take longer than the default maximum execution time of 30 seconds. 

##Overview
The API endpoint is the index.php file. When a client makes a request to the API, the index.php file receives this request. The request is expected to contain a variable called <b>xmldata</b> which contains all of the request XML (formats of which are described later in this documentation). When index.php receives this, it will parse the <b>xmldata</b> variable as a SimpleXML object. Using the value of the "requestType" element, it loads a controller with that value (if one exists and is specified in config.php). So if the value of requestType is "extract" then it will load the file controllers/extract.php.

##How to Add a Controller
In the file you created in the controllers directory, make a new class with the same name as the file (except with a capital letter first). This class needs to extend the Controller class. Now you can override the run method and write the code to process the request there and return the output. So going with our keywords example you should have the following:

- A file named keywords.php in the controllers directory
- In keywords.php, a class named Keywords which extends the Controller class
- In the Keywords class, a run method which contains the main processing logic

Now if you try sending a request with requestType set to "keywords" IRIS will complain and say that keywords is not a valid type. So as a last step, in config.php, add 'keywords' to the VALID_REQUEST_TYPES array. Now IRIS will know that "keywords" is a valid controller.

##How to Add a Text Processing extension
For heavy text processing, it is recommended that you write a program in C which your PHP controller can call since text processing would probably be less efficient in PHP. All of the text processing functions are contained within the "extension" directory. The text_processing.c file is the main file. To add a new 

##Error Response
When IRIS encounters an error, this is what it will return:
```
<parameters>
	<error>message</error>
</parameters>
```

#Low Level Functionality
##Some Extra Notes:
As of now, the client id is passed with every request in a clientID element. This will change with the addition of user authentication.

There is also the option of specifying a user id to add an extra layer of page specificity. That way pages are stored per user id, so two different users could have two different pages with the same id both stored on the server

###Format of resource:
- id - unique number as specified by client, this must be unique for each page, we will use this to store in our indices and we will return the page id's
- url - optional, if the page is a webpage, we can fetch the content from a URL
- content - optional, you can specify the content (HTML or plain text) directly in the XML request

###Notes about resources:
Resources are a generalization of an entity of information. A resource can be a webpage or user specified content. A client wanting to send resources to our API will send them in a <b>resourceList</b> element with the required information described below.

For a resource, you must specify either the <b>url</b> (if it is a web page) or the <b>content</b> element with the plain text data.

Furthermore, resources are stored for caching if the <b>persistence</b> element is specified. If persistence is specified, then the pages will be stored on our server for caching, which can reduce response time and allow for more complicated requests (involving Indri indices). However, this means that each resource must be uniquely identified. 

Identification of a resource happens on two or three levels. The client using the API will have a client id. Each resource the client sends must include a unique <b>id</b> element. Also there is an optional user id which can be used to differentiate between users on the client's system.

On the initial call to the API, the client will have to specify either the content or URL for all of the pages passed. However, a benefit of enabling persistence on pages is that for any later calls on the same pages, the client will only need to specify the id element.

Controllers which modify content (e.g. filter) will return the content element with a type attribute indicating that it has been modified. For example, calling the filter controller will return content with type="filtered". Even having persistence enabled will not store modified content.

Example request:
```
<persistence>TRUE</persistence>
...
<resourceList>
	<resource>
		<id>page ID</id>
		<url>http://...</url>
	</resource>
	<resource>
		<id>page ID</id>
		<content>Some lengthy text here...</content>
	</resource>
</resourceList>

```
##Merge
Merge requests can easily merge multiple select responses in various ways
###Request
```
<parameters>
	<requestType>merge</requestType>
	<mergeType>union|intersection|difference</mergeType>
	<resourceLists>
		<resourceList>
			<resource>
				<id>id</id>
				<content></content>
			</resource>
			...
		</resourceList>
		<resourceList>
			<resource>
				<id>id</id>
				<content></content>
			</resource>
			...
		</resourceList>
		...
	</resourceLists>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>merge</requestType>
	<resourceList>
		<resource>
				<id>id</id>
				<content></content>
		</resource>
		<resource>
				<id>id</id>
				<content></content>
		</resource>
		...
	</resourceList>	
</parameters>
```
##<a id="Pipe"></a>Pipe
The pipe command allows you to do a unix-like pipe feeding the output of one command into the input of another. You cannot do this by simply taking the XML output of one and passing it to another command since it needs a bit of reformatting. This allows multiple commands to be easily strung together and called repeatedly without much work of the client.

Subtle Rules:

If you pipe to rank/filter/query, and do not supply a wordlist element, it will automatically use the content of the first resource of the first resourceList in queue
###Request
```
<parameters>
	<requestType>pipe</requestType>
	<command>
		(Any of the input formats for the commands)
	</command>
	<command>
		(This command will get the resourceList input from the previous command, therefore it is unnecessary to include a resourceList in this command.)
	</command>
	...
</parameters>
```
###Response
The response will follow the format of the last executed command.

##<a id="Limit"></a>Limit
The limit request allows you to select a subset of results. The offset is optional and defaults to 0.
###Request
```
<parameters>
	<requestType>limit</requestType>
	(<offset>number</offset>)
	<amount>number</amount>
	<resourceList>
		<resource>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>limit</requestType>
	<resourceList>
		<resource>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Sort"></a>Sort (deprecated)
Sort a resource list
This controller uses an older format of resources (and will check for a field element). However, it will be updated to adhere to the new XML format. Until then, it is advised not to use this operator unless you wish to alter it.

###Request
```
<parameters>
	<requestType>sort</requestType>
	<orderby type="desc|asc"> 
		field name (defaults to id)
	</orderby>
	<resourceList>
		<resource>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>sort</requestType>
	<resourceList>
		<resource>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```
##<a id="Extract"></a>Extract
###Request
```
<parameters>
	<requestType>extract</requestType>
	<numWords>number</numWords>
	<resourceList>
		<resource>
			<id>id</id>
			<content>
				data
			</content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>extract</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<keywords>comma,seperated,keywords</keywords>
			<content type="extracted">
				data
			</content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Filter"></a>Filter
###Request
The wordList parameter (optional) are the words you wish to remove from the content

```
<parameters>
	<requestType>filter</requestType>
	<wordList>words</wordList>
	<minLength>number</minLength>
	<maxLength>number</maxLength>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>filter</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<content type="filtered"></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Query"></a>Query
Performs simple queries on documents
###Request
```
<parameters>
	<requestType>query</requestType>
	<wordList>list of words to check</wordList>
	<query>
		<type>eq|ne|lt|gt</type>
		<value>value</value>
		<useStemming>TRUE|FALSE</useStemming>
	</query>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

###Response
```
<parameters>
	<requestType>query</requestType>
	<requestID>number</requestID>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Rank"></a>Rank
Ranks documents based on a supplied list of words. The ranking is based on total number of occurences of the words supplied.
###Request
```
<parameters>
	<requestType>rank</requestType>
	<wordList>list of words to check</wordList>
	<resourceList>
		<resource>
			<id>id</id>
			<rank>number</rank>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="vector_rank"></a>Vector Rank
Ranks documents using a vector model of the wordList you supply and returns a rank based on the cosine similarity of the query and the document
###Request
```
<parameters>
	<requestType>vector_rank</requestType>
	<wordList>list of words to check</wordList>
	<resourceList>
		<resource>
			<id>id</id>
			<rank>number</rank>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="index_insert"></a>Index Insert
###Request
```
<parameters>
	<requestType>index_insert</requestType>
	<indexID>optional</indexID>
	<persistence>TRUE|FALSE</persistence>
	<resourceList>
		<resource>
			<url></url>
		</resource>
		<resource>
			<url></url>
		</resource>
		<resource>
			<url></url>
		</resource>
		<resource>
			<url></url>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<indexID>number</indexID>
	<requestType>index_insert</requestType>
</parameters>
```
##<a id="index_delete"></a>Index Delete
###Request
```
<parameters>
	<requestType>index_delete</requestType>
	<indexID>required</indexID>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<indexID>number</indexID>
	<requestType>index_delete</requestType>
</parameters>
```

##<a id="index_query"></a>Index Query
###Request
The query element is mostly the same as the query element describe in the Indri documentation [here](http://sourceforge.net/p/lemur/wiki/IndriRunQuery/) however, you can only specify one query at the moment.
```
<parameters>
	<requestType>index_query</requestType>
	<indexID>number</indexID>
	<query>
		indri type query
	</query>
</parameters>
```
###Response
The Indri resulting score is the logarithm of the probability, therefore the more negative the score is, the lower the rank, and vice-versa
```
<parameters>
	<requestType>index_query</requestType>
	<requestID>number</requestID>
	<indexID>number</indexID>
	<resourceList>
		<resource>
		 	<score>indri query score (sorted in descending order)</score>
		 	<url>page url</url>
		 </resource>
		...
	</resourceList>
</parameters>
```


##<a id="fetch"></a>Fetch
Fetch gets the content of the passed url's
###Request
```
<parameters>
	<requestType>fetch</requestType>
	<resourceList>
		<resource>
			<url>page url</url>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestType>fetch</requestType>
	<requestID>number</requestID>
	<resourceList>
		<resource>
			<url>page url</url>
			<content>page content</content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="extract_blocks"></a>Extract Blocks
###Request
```
<parameters>
	<requestType>extract_blocks</requestType>
	<wordList></wordList>
	<searchWindow>num of words</searchWindow>
	<resultWindow>num of words</resultWindow>
	<useStemming>TRUE|FALSE</useStemming>
	<resourceList>
		<resource>
			<url>page url</url>
			<content>page content</content>
		</resource>
		...
	</resourceList>
</parameters>
```

###Response
```
<parameters>
	<requestType>extract_blocks</requestType>
	<requestID>number</requestID>
	<resourceList>
		<resource>
			<url>page url</url>
			<blockList>
				<block>
					block text
				</block>
				...
			</blockList>
			<content>page content</content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="summarize_sentences"></a>Summarize Sentences
This simple summarization controller will use the words in the wordList passed and deem the most important sentences of a document by which those words appear the most.

At the moment, the word list can have repeated words (which will weight those words higher). This will most likely be removed for consistency.
```
<parameters>
	<requestType>summarize_sentences</requestType>
	<numSentences>number</numSentences>
	<wordList></wordList>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>summarize_sentences</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<content type='summarized'></content>
		</resource>
		...
	</resourceList>
</parameters>
```


##If Then
Perform a basic if-then control statement. 

The val element can be in [xpath](http://www.php.net/manual/en/simplexmlelement.xpath.php) format to access XML nodes or a literal number/string.

You can test the number of nodes returned by setting fxn to "length". You can also test for the existence of a node by using the "exists" test. Exists only requires 1 val element. Otherwise all of the other operators require 2 val elements. The nth attribute can be used to get a specific node.

The motivation behind this is for the if-then control to be used in conjunction with pipe requests to reduce total number of requests. This can be seen under examples.

###Request
```
<parameters>
	<requestType>if_then</requestType>
	<if>
		<statement>
			<val type="xpath|literal" nth="" fxn="length">value (optional)</val>
			<test>eq|ne|lt|lte|gt|gte|exists (optional)</test>
			<val type="xpath|literal" nth="" fxn="length">value (optional)</val>
		</statement>
		<command>
			...
		</command>
	</if>
	<elif>
		(optional)
		<statement>
			...
		</statement>
		<command>
			...
		</command>
	</elif>
	<else>
		(optional)
		<command>
			...
		</command>
	</else>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
The response will be the response of the command executed from the structure OR if no command is executed (e.g. if statement is false and no else is specified) it will return the following:
```
<parameters>
	<requestType>if_then</requestType>
	<requestID></requestID>
	<status>No branch taken</status>
</parameters>
```

##Halt
The halt operator stops execution and is supposed to be used in conjunction with pipe requests and if_then requests to stop piping if certain conditions are met.
###Request
```
<parameters>
	<requestType>halt</requestType>
</parameters>
```
###Response
```
<parameters>
	<requestType>halt</requestType>
	<requestID></requestID>
</parameters>
```