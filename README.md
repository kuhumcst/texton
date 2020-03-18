# Text Tonsorium     A salon de beauté for Natural Language Processing

The Text Tonsorium is a web application that not only executes workflows, but also composes workflows from building blocks. 
Each building block encapsulates a Natural Language Processing tool.

The Text Tonsorium may compose many workflows that all lead to your goal. 
It will then ask you to choose one of the proposed workflows.

The original version of the Text Tonsorium was made during the [DK-Clarin project](https://dkclarin.ku.dk/).
The Text Tonsorium is written in the [Bracmat](https://github.com/BartJongejan/Bracmat) programming language, except for the communication with the user's browser and with the tools (web services in their own right), which is implemented in Java.

The file textonInstall.txt gives a detailed description of how to install the Text Tonsorium in the Windows Subsystem for Linux.

A number of NLP tools are included in this repositorium. More tools can by tried at the [Text Tonsorium homepage](https://nlpweb01.nors.ku.dk/texton/).
Resources for the NLP tools must be separately fetched from [GitHub](https://github.com/kuhumcst/texton-linguistic-resources). 

## Bibliography

### Brill tagger
**Cite**:Eric Brill. 1992. A simple rule-based part of speech tagger. In *Proceedings of the third conference on Applied natural language processing (ANLC '92)*.
Association for Computational Linguistics, Stroudsburg, PA, USA, 152-155. doi:10.3115/974499.974526

### Lapos tagger
*Cite*: Yoshimasa Tsuruoka, Yusuke Miyao, and Jun'ichi Kazama. 2011. Learning with Lookahead: Can History-Based Models Rival Globally Optimized Models? In *Proceedings of CoNLL*, pp. 238-246.

### CSTlemma
Jongejan, B. and Dalianis, H. (2009). Automatic training
of lemmatization rules that handle morphological
changes in pre-, in- and suffixes alike. In *Proceedings
of the Joint Conference of the 47th Annual Meeting of
the ACL and the 4th International Joint Conference on
Natural Language Processing of the AFNLP*, volume 1,
pages 145–153. Association for Computational Linguistics.

