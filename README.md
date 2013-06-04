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

##Summarization
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
##Clustering
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
		table name (webpage|annotation|snippet|bookmarks|searches)
	</table>
	<where>
		(<and>|<or>|<not>)
			<field operator="=|>|<|>=|<=|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			<field operator="=|>|<|>=|<=|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			...
		(</and>|</or>|</not>)
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
The field operator allows you to select from predefined fields based on the table (e.g. you can add a field of "url" or "snippetID" if the table value is "snippet").

The &lt;and&gt;, &lt;or&gt;, and &lt;not&gt; tags wrap fields in the &lt;where&gt; clause for logical connectives.

###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>select</requestType>
	<resourceList>
		<resource>
			<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
			<id>id</id>
			(<fields>
				...
			</fields>)
		</resource>
		<resource>
			<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
			<id>id</id>
			(<fields>
				...
			</fields>)
		</resource>
		...
	</resourceList>
</parameters>
```
###Some examples
Select the url from a pageID of 10 in the webpage table

Request:
```
<parameters>
	<requestType>select</requestType>
	<fields>
		<field>
			url
		</field>
		<field>
			pageID
		</field>
	</fields>
	<table>
		webpage
	</table>
	<where>
			<field operator="=">
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
			<id>10</id>
			<fields>
				<url>http://www.google.com</url>
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
				<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
				<id>id</id>
				(<fields>
					...
				</fields>)
			</resource>
			<resource>
				<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
				<id>id</id>
				(<fields>
					...
				</fields>)
			</resource>
			...
		</resourceList>
		<resourceList>
			<resource>
				<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
				<id>id</id>
				(<fields>
					...
				</fields>)
			</resource>
			<resource>
				<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
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
			<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
			<id>id</id>
			(<fields>
				...
			</fields>)
		</resource>
		<resource>
			<type>table name (webpage|annotation|snippet|bookmarks|searches)</type>
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
	<resource>
		<table>table name</table>
		<id>id</id>
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
	</resource>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>update</requestType>
	<status>success|error</status>
	<message>error message</message>
	<resource>
		<type>table name</type>
		<id>id</id>
	</resource>
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
