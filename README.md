#Directory Structure
- bin/ - Lemur executable files (compiled on Ubuntu)
- controllers/ - files which perform specific tasks (e.g. clustering, summarization, etc.)
- library/ - helpful methods shared among all controllers
- storage/ - folder where all of the request/response data is stored, where all of the necessary indexing files are created (and removed)
- config.php - configuration for debugging and file paths
- dbconfig.php.example - configuration file for database (remove the trailing .example to use)

#Adding a new controller to the API
To add a new controller, add the file to the controllers directory, give it the same name as the request type. E.G. if you wanted to add a controller for 'keywords' add a file keywords.php to controllers. Then, in index.php, add 'keywords' to the validTypes array. An example input that would access that controller would look like:
```
<parameters>
<requestType>keywords</requestType>
... (other parameters)
</parameters>
```
#Request and Response Format:

##Summarization
###Request
```
<parameters>
	<requestType>summarize</requestType>
	<maxSentences>10</maxSentences>
	<individualSummaries>TRUE</individualSummaries>
	<docList>
		<doc>
			<docID>1</docID>
		</doc>
		<doc>
			<docID>2</docID>
		</doc>
		<doc>
			<docID>3</docID>
		</doc>
	</docList>
</parameters>

```
Here,
- &lt;maxSentences&gt;: up to how many words to produce in the summary
- &lt;individualSummaries&gt;: create individual summaries for each document? Here it's set to "TRUE", which means you'd output three summaries (there are three documents).

###Response
```
<parameters>
	<requestID>123</requestID>
	<requestType>summarize</requestType>
	<docList>
	<doc>
	<docID>1</docID>
		<summary>
		stuff
		</summary>
	</doc>
	<doc>
	<docID>2</docID>
		<summary>
		stuff
		</summary>
	</doc>
	<doc>
	<docID>3</docID>
		<summary>
		stuff
		</summary>
	</doc>
	</docList>
</parameters>

```
##Clustering
###Request
```
<parameters>
	<requestType>cluster</requestType>
	<numClusters>3</numClusters>
	<docList>
		<doc>
			<docID>1</docID>
		</doc>
		<doc>
			<docID>2</docID>
		</doc>
		<doc>
			<docID>3</docID>
		</doc>
		<doc>
			<docID>4</docID>
		</doc>
		<doc>
			<docID>5</docID>
		</doc>
		<doc>
			<docID>6</docID>
		</doc>
	</docList>
</parameters>

```

###Response
```
<parameters>
	<requestID>123</requestID>
	<requestType>cluster</requestType>
	<clusterList>
	<cluster>
		<clusterID>1</clusterID>
		<docList>
			<doc>
				<docID>1</docID>
				<title>abc</title>
			</doc>
				<docID>3</docID>
				<title>xyz</title>
			</doc>
		</docList>
	</cluster>
	<cluster>
		<clusterID>2</clusterID>
		<docList>
			<doc>
				<docID>2</docID>
				<title>abc</title>
			</doc>
				<docID>6</docID>
				<title>xyz</title>
			</doc>
		</docList>
	</cluster>
	<cluster>
		<clusterID>3</clusterID>
		<docList>
			<doc>
				<docID>4</docID>
				<title>abc</title>
			</doc>
				<docID>5</docID>
				<title>xyz</title>
			</doc>
		</docList>
	</cluster>
</parameters>
```

##Error
###Response
```
<parameters>
	<error>message</error>
</parameters>
```

#Low Level Functionality (in progress)
##Select
###Request
The field operator allows you to select from predefined fields based on the table (e.g. you can add a field of "url" or "snippetID" if the table value is "snippet").
Not including the fields list will return no fields, but can still be useful for only retrieving the id's of the resulting resources.

The &lt;and&gt;, &lt;or&gt;, and &lt;not&gt; tags wrap fields in the &lt;where&gt; clause for logical connectives.
```
<parameters>
	<requestType>select</requestType>
	<fields>
		<field>
			field name
		</field>
		<field>
			field name
		</field>
		...
	</fields>
	<table>
		table name (pages|annotation|snippet|bookmarks|searches)
	</table>
	<where>
		(<logic type="and|or|not")
			<field operator="eq|ne|lt|gt|lte|gte|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			<field operator="eq|ne|lt|gt|lte|gte|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			...
		(</logic>)
		...
	</where>
	(<orderby type="desc|asc">
		<field>
			field name
		</field>
	 </orderby>)
	 (<limit>
	 	number
	 (</limit)
</parameters>
```

###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>select</requestType>
	<resourceList>
		<resource>
			<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
			(<fields>
				...
			</fields>)
		</resource>
		<resource>
			<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
			(<fields>
				...
			</fields>)
		</resource>
		...
	</resourceList>
</parameters>
```
###Select example
Select the url from a pageID of 10 in the pages table

Request:
```
<parameters>
	<requestType>select</requestType>
	<fields>
		<field>url</field>
		<field>pageID</field>
	</fields>
	<table>pages</table>
	<where>
			<field operator="eq">
				<name>
					pageID
				</name>
				<value>
					10
				</value>
			</field>
	</where>
</parameters>
```
Response:
```
<parameters>
	<requestID>20841</requestID>
	<requestType>select</requestType>
	<resourceList>
		<resource>
			<type>snippet</type>
			<fields>
				<field>
					<name>pageID</name>
					<value>10</value>
				</field>
				<field>
					<name>url</name>
					<value>http://www.google.com</value>
				</field>
			</fields>
		</resource>
	</resourceList>
</parameters>
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
				<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
				<id>id</id>
				(<fields>
					...
				</fields>)
			</resource>
			<resource>
				<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
				<id>id</id>
				(<fields>
					...
				</fields>)
			</resource>
			...
		</resourceList>
		<resourceList>
			<resource>
				<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
				<id>id</id>
				(<fields>
					...
				</fields>)
			</resource>
			<resource>
				<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
				<id>id</id>
				(<fields>
					...
				</fields>)
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
			<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
			<id>id</id>
			(<fields>
				...
			</fields>)
		</resource>
		<resource>
			<type>table name (pages|annotation|snippet|bookmarks|searches)</type>
			<id>id</id>
			(<fields>
				...
			</fields>)
		</resource>
		...
	</resourceList>	
</parameters>
```
##Insert
###Request
```
<parameters>
	<requestType>insert</requestType>
	<table>table name</table>
	<fields>
		<field>
			<name>
				field name
			</name>
			<value>
				field value
			</value>
		</field>
		...
	</fields>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>insert</requestType>
	<status>success|error</status>
	<message>error message</message>
	<resource>
		<type>table name</type>
		<id>id</id>
	</resource>
</parameters>
```
##Update
###Request
```
<parameters>
	<requestType>update</requestType>
	<fields>
		<field>
			<name>
				field name
			</name>
			<value>
				field value
			</value>
		</field>
		...
	</fields>
	<resourceList>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>update</requestType>
	<status>success|error</status>
	<message>error message</message>
	<resourceList>
		...
	</resourceList>
</parameters>
```
##Delete
###Request
```
<parameters>
	<requestType>delete</requestType>
	<resourceList>
		<resource>
			<table>table name</table>
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
	<requestType>delete</requestType>
	<status>success|error</status>
	<message>error message</message>
</parameters>
```

##Pipe
The pipe command allows you to do a unix-like pipe feeding the output of one command into the input of another. You cannot do this by simply taking the XML output of one and passing it to another command since it needs a bit of reformatting. This allows multiple commands to be easily strung together and called repeatedly without much work of the client.
###Request
```
<parameters>
	<requestType>pipe</requestType>
	<commandList>
		<command>
			(Any of the input formats for the commands)
		</command>
		<command>
			(This command will get the resourceList input from the previous command, therefore it is unnecessary to include a resourceList in this command.)
		</command>
		...
	</commandList>
</parameters>
```
###Response
The response will follow the format of the last executed command.
###Pipe Examples
####Example 1
This example calls select on snippets with ids from 10 to 20 and then deletes the results

Notice that the delete command is missing the resourceList since it will be automatically filled by the output of the select statement.
```
<parameters>
	<requestType>pipe</requestType>
	<commandList>
		<command>
			<parameters>
				<requestType>select</requestType>
				<table>snippets</table>
				<where>
						<logic type="and">
							<field operator="gte">
								<name>snippetID</name>
								<value>10</value>
							</field>
							<field operator="lte">
								<name>snippetID</name>
								<value>20</value>
							</field>
						</logic>
				</where>
			</parameters>
		</command>
		<command>
			<parameters>
				<requestType>delete</requestType>
			</parameters>
		</command>
	</commandList>
</parameters>
```
####Example 2
This example merges two select statements and updates the projectID
```
<parameters>
	<requestType>pipe</requestType>
	<commandList>
		<command>
			<parameters>
				<requestType>select</requestType>
				<table>snippets</table>
				<where>
						<field operator="eq">
							<name>snippetID</name>
							<value>10</value>
						</field>
				</where>
			</parameters>
		</command>
		<command>
			<parameters>
				<requestType>select</requestType>
				<table>pages</table>
				<where>
					<field operator="eq">
						<name>pageID</name>
						<value>13</value>
					</field>
				</where>
			</parameters>
		</command>
		<command>
			<parameters>
				<requestType>merge</requestType>
				<mergeType>union</mergeType>
			</parameters>
		</command>
		<command>
			<parameters>
				<requestType>update</requestType>
				<fields>
					<field>
						<name>projectID</name>
						<value>5</value>
					</field>
				</fields>
			</parameters>
		</command>
	</commandList>
</parameters>
```

##Limit
The limit request allows you to select a subset of results. The offset is optional and defaults to 0.
###Request
```
<parameters>
	<requestType>limit</requestType>
	(<offset>number</offset>)
	<amount>number</amount>
	<resourceList>
		<resource>
			<table>table name</table>
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
			<table>table name</table>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```

##Sort
Sort a resource list
###Request
```
<parameters>
	<requestType>sort</requestType>
	<orderby type="desc|asc">
		field name
	</orderby>
	<resourceList>
		<resource>
			<table>table name</table>
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
			<table>table name</table>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```