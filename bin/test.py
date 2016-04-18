import sys
content = sys.argv[1]

import nltk
nltk.data.path.append('/home/rajvi/nltk_data');

words= nltk.word_tokenize(content)
tagged=nltk.pos_tag(words)

for ps in tagged:

	print "<tagged>"
	print "<pos>" + ps[1] + "</pos>"
	print "<word>" + ps[0] + "</word>"
	print "</tagged>"

