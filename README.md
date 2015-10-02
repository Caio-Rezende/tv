# tv
It's a client/server sides for searching content in the web and displaying them customizably

use it in a php server and it will work out of the box, just by running the index.php in the root folder.

It is currently retrieving content from BBC, TechTudo, Exame and IGN, all from brazilian branches.

If you would like to add more sources to retrieve content, just add more media in the medias folder, they all have a similar structure and extend the fromMedia class in the class folder.
The added media must have the php file name equal to the class name, appended with 'from' to the class name. See the ones already created and it will be clear.
Try looking in the ajax/sources.php and you will see how the classes and and files names are composed to use dynamically.