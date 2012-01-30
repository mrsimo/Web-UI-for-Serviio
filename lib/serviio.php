<?php
# http://www.gen-x-design.com/archives/making-restful-requests-in-php/

class ServiioService extends RestRequest {

    protected $host;
    protected $port;

    public $error;
    public $warning;

    public $searchHiddenFiles;
    public $searchForUpdates;
    public $automaticLibraryUpdate;
    public $automaticLibraryUpdateInterval;
    public $maxNumberOfItemsForOnlineFeeds;
    public $onlineFeedExpiryInterval;
    public $onlineContentPreferredQuality;
    public $repository;

    public $profiles;
    public $renderers;

    public $audioLocalArtExtractorEnabled;
    public $videoLocalArtExtractorEnabled;
    public $videoOnlineArtExtractorEnabled;
    public $videoGenerateLocalThumbnailEnabled;
    public $metadataLanguage;
    public $descriptiveMetadataExtractor;
    public $retrieveOriginalTitle;

    public $descriptiveMetadataExtractors;
    public $browsingCategoriesLanguages;

    public $audioDownmixing;
    public $threadsNumber;
    public $transcodingFolderLocation;
    public $transcodingEnabled;
    public $bestVideoQuality;

    public $numberOfCPUCores;

    public $presentationLanguage;
    public $showParentCategoryTitle;

    /************************************************/
    public function __construct ($host,$port) {
        parent::flush();
        $this->host = $host;
        $this->port = $port;
    }

    /************************************************/
    public function getStatus() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/status');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get status";
            return false;
        }
        $serverStatus = (string)$xml->serverStatus;
        $ip = (string)$xml->boundIPAddress;
        $this->renderers = array();
        foreach ($xml->renderers->renderer as $item) {
            $uuid = (string)$item->uuid;
            $ipAddress = (string)$item->ipAddress;
            $name = (string)$item->name;
            $profileId = (string)$item->profileId;
            $status = (string)$item->status;
            $this->renderers[$uuid] = array($ipAddress,$name,$profileId,$status);
        }
        return array($serverStatus,$this->renderers,$ip);
    }

    /************************************************/
    public function getPing() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/ping');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get ping";
            return false;
        }
        if($xml->errorCode == 0) {
            return true;
        } else {
            return false;
        }
    }

    /************************************************/
    public function getServiceStatus() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/service-status');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get service status";
            return false;
        }
        $xmlarray = array(); // this will hold the flattened data
        XMLToArrayFlat($xml, $xmlarray, '', true); 
        return $xmlarray;
    }

    /************************************************/
    public function getApplication() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/application');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get application";
            return false;
        }
        return (string)$xml->version;
    }

    /************************************************/
    public function getRepository() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/repository');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get repository";
            return false;
        }
        $repo = array();
        $sf = array();
        $or = array();

        foreach ($xml->sharedFolders as $sharedFolders) {
            foreach ($sharedFolders as $item) {
                $id = (string)$item->id;
                $folderPath = (string)$item->folderPath;
                $supportedFileTypes = array();
                foreach ($item->supportedFileTypes as $types) {
                    foreach ($types as $type) {
                        $supportedFileTypes[] = (string)$type;
                    }
                }
                $descriptiveMetadataSupported = (string)$item->descriptiveMetadataSupported;
                $scanForUpdates = (string)$item->scanForUpdates;
                $sf[$id] = array($folderPath,$supportedFileTypes,$descriptiveMetadataSupported,$scanForUpdates);
            }
        }
        $repo[0] = $sf;

        $this->searchHiddenFiles = (string)$xml->searchHiddenFiles;
        $this->searchForUpdates = (string)$xml->searchForUpdates;
        $this->automaticLibraryUpdate = (string)$xml->automaticLibraryUpdate;
        $this->automaticLibraryUpdateInterval = (string)$xml->automaticLibraryUpdateInterval;

        // onlineRepositories
        foreach ($xml->onlineRepositories as $onlineRepositories) {
            foreach ($onlineRepositories as $item) {
                $id = (string)$item->id;
                $repositoryType = (string)$item->repositoryType;
                $contentUrl = (string)$item->contentUrl;
                $fileType = (string)$item->fileType;
                $thumbnailUrl = (string)$item->thumbnailUrl;
                $repositoryName = (string)$item->repositoryName;
                $enabled = (string)$item->enabled;
                $or[$id] = array($repositoryType,$contentUrl,$fileType,$thumbnailUrl,$repositoryName,$enabled);
            }
        }
        $repo[1] = $or;
        $this->maxNumberOfItemsForOnlineFeeds = (string)$xml->maxNumberOfItemsForOnlineFeeds;
        $this->onlineFeedExpiryInterval = (string)$xml->onlineFeedExpiryInterval;
        $this->onlineContentPreferredQuality = (string)$xml->onlineContentPreferredQuality;

        $this->repository = $repo;
        return $repo;
    }

    // /rest/library-status

    /************************************************/
    public function getProfiles() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/refdata/profiles');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get profiles";
            return false;
        }
        $profiles = array();
        foreach ($xml->values->item as $item) {
            $value = (string)$item->value;
            $name = (string)$item->name;
            $profiles["${name}"] = $value;
        }
        $this->profiles = $profiles;
        return $profiles;
    }

    /************************************************/
    public function getMetadata() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/metadata');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get metadata";
            return false;
        }
        $audioLocalArtExtractorEnabled = (string)$xml->audioLocalArtExtractorEnabled;
        $videoLocalArtExtractorEnabled = (string)$xml->videoLocalArtExtractorEnabled;
        $videoOnlineArtExtractorEnabled = (string)$xml->videoOnlineArtExtractorEnabled;
        $videoGenerateLocalThumbnailEnabled = (string)$xml->videoGenerateLocalThumbnailEnabled;
        $metadataLanguage = (string)$xml->metadataLanguage;
        $retrieveOriginalTitle = (string)$xml->retrieveOriginalTitle;
        $descriptiveMetadataExtractor = (string)$xml->descriptiveMetadataExtractor;
        $this->audioLocalArtExtractorEnabled = $audioLocalArtExtractorEnabled;
        $this->videoLocalArtExtractorEnabled = $videoLocalArtExtractorEnabled;
        $this->videoOnlineArtExtractorEnabled = $videoOnlineArtExtractorEnabled;
        $this->videoGenerateLocalThumbnailEnabled = $videoGenerateLocalThumbnailEnabled;
        $this->metadataLanguage = $metadataLanguage;
        $this->retrieveOriginalTitle = $retrieveOriginalTitle;
        $this->descriptiveMetadataExtractor = $descriptiveMetadataExtractor;
        return array($audioLocalArtExtractorEnabled,$videoLocalArtExtractorEnabled,$videoOnlineArtExtractorEnabled,$videoGenerateLocalThumbnailEnabled,$metadataLanguage,$descriptiveMetadataExtractor,$retrieveOriginalTitle);
    }

    /************************************************/
    public function getDescriptiveMetadataExtractors() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/refdata/descriptiveMetadataExtractors');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get descriptive metadata extractors";
            return false;
        }
        $result = array();
        foreach ($xml->values->item as $item) {
            $value = (string)$item->value;
            $name = (string)$item->name;
            $result["${name}"] = $value;
        }
        $this->descriptiveMetadataExtractors = $result;
        return $result;
    }

    /************************************************/
    public function getBrowsingCategoriesLanguages() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/refdata/browsingCategoriesLanguages');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get browsing categories languages";
            return false;
        }
        $result = array();
        foreach ($xml->values->item as $item) {
            $value = (string)$item->value;
            $name = (string)$item->name;
            $result["${name}"] = $value;
        }
        $this->browsingCategoriesLanguages = $result;
        return $result;
    }

    /************************************************/
    public function getTranscoding() {
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/transcoding');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get transcoding";
            return false;
        }
        $audioDownmixing = (string)$xml->audioDownmixing;
        $threadsNumber = (string)$xml->threadsNumber;
        $transcodingFolderLocation = (string)$xml->transcodingFolderLocation;
        $transcodingEnabled = (string)$xml->transcodingEnabled;
        $bestVideoQuality = (string)$xml->bestVideoQuality;
        $this->audioDownmixing = $audioDownmixing;
        $this->threadsNumber = $threadsNumber;
        $this->transcodingFolderLocation = $transcodingFolderLocation;
        $this->transcodingEnabled = $transcodingEnabled;
        $this->bestVideoQuality = $bestVideoQuality;
        return array($audioDownmixing,$threadsNumber,$transcodingFolderLocation,$transcodingEnabled,$bestVideoQuality);
    }

    /************************************************/
    public function getCpuCores() {
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/refdata/cpu-cores');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get CPU cores";
            return false;
        }
        $numberOfCPUCores = 1;
        foreach ($xml->values as $entry) {
            $item = $entry->item;
            if ((string)$item->name=="numberOfCPUCores") {
                $numberOfCPUCores = (string)$item->value;
                break;
            }
        }
        $this->numberOfCPUCores = $numberOfCPUCores;
        return $numberOfCPUCores;
    }

    /************************************************/
    public function getPresentation() {
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/presentation');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get presentation";
            return false;
        }
        $categories = array();
        foreach ($xml->categories->browsingCategory as $entry) {
            $id = (string)$entry->id; // => A
            $title = (string)$entry->title; // => Audio
            $visibility = (string)$entry->visibility; // => DISPLAYED
            $subCategories = array();
            foreach ($entry->subCategories->browsingCategory as $item) {
                $subId = (string)$item->id; // => A_F
                $subTitle = (string)$item->title; // => Folders
                $subVisibility = (string)$item->visibility; // => DISPLAYED
                $subCategories[$subId] = array($subTitle,$subVisibility);
            }
            $categories[$id] = array($title,$visibility,$subCategories);
        }
        $presentationLanguage = (string)$xml->language;
        $showParentCategoryTitle = (string)$xml->showParentCategoryTitle;
        $this->presentationLanguage = $presentationLanguage;
        $this->showParentCategoryTitle = $showParentCategoryTitle;
        return $categories;
    }

    /************************************************/
    public function getCategoryVisibilityTypes() {
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/refdata/categoryVisibilityTypes');
        parent::setVerb('GET');
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->error = "Cannot get presentation";
            return false;
        }
        $types = array();
        foreach ($xml->values->item as $entry) {
            $types[(string)$entry->name] = (string)$entry->value;
        }
        return $types;
    }









    /************************************************/
    public function putStatus($profiles,$ip) {
        // create the xml document
        $xmlDoc = new DOMDocument();

        // add encoding
        $xmlDoc->encoding = "UTF-8";

        //create the root element
        $root = $xmlDoc->appendChild($xmlDoc->createElement("status"));

        // create sub element
        $root->appendChild($xmlDoc->createElement("boundIPAddress", $ip));
        $Rends = $root->appendChild($xmlDoc->createElement("renderers"));

        foreach ($profiles as $renderer) {
            $Rend = $Rends->appendChild($xmlDoc->createElement("renderer"));
            $Rend->appendChild($xmlDoc->createElement("uuid", $renderer[0]));
            $Rend->appendChild($xmlDoc->createElement("ipAddress", $renderer[1]));
            $Rend->appendChild($xmlDoc->createElement("name", $renderer[2]));
            $Rend->appendChild($xmlDoc->createElement("profileId", $renderer[3]));
        }

//        header("Content-Type: text/plain");
//        $xmlDoc->formatOutput = true;
//        $requestBody = $xmlDoc->saveXML();
//        echo $requestBody;
//        die();

/*
    $requestBody = '<?xml version="1.0" encoding="UTF-8" ?>
<status>
  <boundIPAddress>'.$ip.'</boundIPAddress>
  <renderers>';
        foreach ($profiles as $renderer) {
            $requestBody.= '
    <renderer>
      <uuid>'.$renderer[0].'</uuid>
      <ipAddress>'.$renderer[1].'</ipAddress>
      <name>'.$renderer[2].'</name>
      <profileId>'.$renderer[3].'</profileId>
    </renderer>';
    }
        $requestBody.= '
  </renderers>
</status>';
*/
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/status');
        parent::setVerb('PUT');
        parent::setRequestBody($xmlDoc->SaveXML());
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->warning = "Cannot put status";
            return false;
        }
        return (string)$xml->errorCode;
    }

    /************************************************/
    public function postAction($action) {
        // Nasty bug http://restlet.tigris.org/issues/show_bug.cgi?id=1186
        $requestBody = '<?xml version="1.0" encoding="UTF-8" ?>
    <action>
      <name>'.$action.'</name>
    </action>';
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/action');
        parent::setVerb('POST');
        parent::setRequestBody($requestBody);
        parent::execute();
        $str = parent::getResponseBody();
        if (strpos($str,'<title>Status page</title>')) {
            $str = strstr($str,'<h3>');
            $this->warning = substr($str,0,strpos($str,'</body>'));
            return false;
        }
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->warning = "Cannot post action: ".$action;
            return false;
        }
        return (string)$xml->errorCode;
    }
    
    /************************************************/
    public function putTranscoding($transcoding,$location,$cores,$audio,$quality) {
        $requestBody = '<?xml version="1.0" encoding="UTF-8" ?>
<transcoding>
  <audioDownmixing>'.$audio.'</audioDownmixing>
  <threadsNumber>'.$cores.'</threadsNumber>
  <transcodingFolderLocation>'.$location.'</transcodingFolderLocation>
  <bestVideoQuality>'.$quality.'</bestVideoQuality>
  <transcodingEnabled>'.$transcoding.'</transcodingEnabled>
</transcoding>';
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/transcoding');
        parent::setVerb('PUT');
        parent::setRequestBody($requestBody);
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->warning = "Cannot put transcoding";
            return false;
        }
        return (string)$xml->errorCode;    
    }

    /************************************************/
    public function putMetadata($audioLocalArtExtractorEnabled,$videoLocalArtExtractorEnabled,$videoOnlineArtExtractorEnabled,
    $videoGenerateLocalThumbnailEnabled,$metadataLanguage,$descriptiveMetadataExtractor,$retrieveOriginalTitle) {
        $requestBody = '<?xml version="1.0" encoding="UTF-8" ?>
<metadata>
  <audioLocalArtExtractorEnabled>'.$audioLocalArtExtractorEnabled.'</audioLocalArtExtractorEnabled>
  <videoLocalArtExtractorEnabled>'.$videoLocalArtExtractorEnabled.'</videoLocalArtExtractorEnabled>
  <videoOnlineArtExtractorEnabled>'.$videoOnlineArtExtractorEnabled.'</videoOnlineArtExtractorEnabled>
  <videoGenerateLocalThumbnailEnabled>'.$videoGenerateLocalThumbnailEnabled.'</videoGenerateLocalThumbnailEnabled>
  <metadataLanguage>'.$metadataLanguage.'</metadataLanguage>
  <retrieveOriginalTitle>'.$retrieveOriginalTitle.'</retrieveOriginalTitle>
  <descriptiveMetadataExtractor>'.$descriptiveMetadataExtractor.'</descriptiveMetadataExtractor>
</metadata>';
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/metadata');
        parent::setVerb('PUT');
        parent::setRequestBody($requestBody);
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->warning = "Cannot put metadata";
            return false;
        }
        return (string)$xml->errorCode;    
    }

    /************************************************/
    public function putRepository($repo) {
        //create the xml document
        $xmlDoc = new DOMDocument();

        // add encoding
        $xmlDoc->encoding = "UTF-8";

        //create the root element
        $root = $xmlDoc->appendChild($xmlDoc->createElement("repository"));

        //create a tutorial element
        $sharedFolders = $root->appendChild($xmlDoc->createElement("sharedFolders"));

        /* FOLDERS */
        foreach ($repo[0] as $id=>$entry) {
            $Folder = $sharedFolders->appendChild($xmlDoc->createElement("sharedFolder"));
            if ($id <= $entry[4])
                $Folder->appendChild($xmlDoc->createElement("id", $id));
            $Folder->appendChild($xmlDoc->createElement("folderPath", $entry[0]));

            $supportedFileTypes = $Folder->appendChild($xmlDoc->createElement("supportedFileTypes"));
            foreach ($entry[1] as $type) {
                $supportedFileTypes->appendChild($xmlDoc->createElement("fileType", $type));
            }

            $Folder->appendChild($xmlDoc->createElement("descriptiveMetadataSupported", $entry[2]));
            $Folder->appendChild($xmlDoc->createElement("scanForUpdates", $entry[3]));
        }
        $root->appendChild($xmlDoc->createElement("searchHiddenFiles", $this->searchHiddenFiles));
        $root->appendChild($xmlDoc->createElement("searchForUpdates", $this->searchForUpdates));
        $root->appendChild($xmlDoc->createElement("automaticLibraryUpdate", $this->automaticLibraryUpdate));
        $root->appendChild($xmlDoc->createElement("automaticLibraryUpdateInterval", $this->automaticLibraryUpdateInterval));

        /* Online Repositories */
        $sharedFolders = $root->appendChild($xmlDoc->createElement("onlineRepositories"));
        if (isset($repo[1])) {
            foreach ($repo[1] as $id=>$entry) {
                $Folder = $sharedFolders->appendChild($xmlDoc->createElement("onlineRepository"));
                if ($id <= $entry[3]) {
                    $Folder->appendChild($xmlDoc->createElement("id", $id));
                }
                $Folder->appendChild($xmlDoc->createElement("repositoryType", $entry[0]));
                $Folder->appendChild($xmlDoc->createElement("contentUrl", str_replace("&", "&amp;", $entry[1])));
                $Folder->appendChild($xmlDoc->createElement("fileType", $entry[2]));
                $Folder->appendChild($xmlDoc->createElement("thumbnailUrl"));
                $Folder->appendChild($xmlDoc->createElement("repositoryName", $entry[4]));
                $Folder->appendChild($xmlDoc->createElement("enabled", $entry[5]));
            }
        }
        $root->appendChild($xmlDoc->createElement("maxNumberOfItemsForOnlineFeeds", $this->maxNumberOfItemsForOnlineFeeds));
        $root->appendChild($xmlDoc->createElement("onlineFeedExpiryInterval", $this->onlineFeedExpiryInterval));
        $root->appendChild($xmlDoc->createElement("onlineContentPreferredQuality", $this->onlineContentPreferredQuality));


//    header("Content-Type: text/plain");
//    $xmlDoc->formatOutput = true;
//    $requestBody = $xmlDoc->saveXML();
//    echo $requestBody;
//    die();

        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/repository');
        parent::setVerb('PUT');
        parent::setRequestBody($xmlDoc->saveXML());
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->warning = "Cannot put repository";
            return false;
        }

        return (string)$xml->errorCode;        
    }

    /************************************************/
    public function putPresentation($categories,$presentationLanguage,$showParentCategoryTitle) {
        $requestBody = '<?xml version="1.0" encoding="UTF-8" ?>
<presentation>
  <categories>';
        foreach ($categories as $id=>$category) {
            $id = str_replace("'","",$id);
            $requestBody.= '
    <browsingCategory>
      <id>'.$id.'</id>
      <visibility>'.$category[1].'</visibility>
      <subCategories>';
            foreach ($category[2] as $subId=>$subCategory) {
                $subId = str_replace("'","",$subId);
                $requestBody.= '
        <browsingCategory>
    <id>'.$subId.'</id>
    <visibility>'.$subCategory[1].'</visibility>
    <subCategories/>
    </browsingCategory>';
            }
            $requestBody.= '
      </subCategories>
    </browsingCategory>';
        }
        $requestBody.= '
  </categories>
  <language>'.$presentationLanguage.'</language>
  <showParentCategoryTitle>'.$showParentCategoryTitle.'</showParentCategoryTitle>
</presentation>';
        parent::flush();
        parent::setUrl('http://'.$this->host.':'.$this->port.'/rest/presentation');
        parent::setVerb('PUT');
        parent::setRequestBody($requestBody);
        parent::execute();
        $xml = simplexml_load_string(parent::getResponseBody());
        if($xml===false) {
            $this->warning = "Cannot put presentation";
            return false;
        }
        return (string)$xml->errorCode;
    }
}

/************************************************/
/************************************************/
/************************************************/
/************************************************/
/************************************************/
//
//$xmlarray = array(); // this will hold the flattened data
//XMLToArrayFlat($xml, $xmlarray, '', true); 
//print_r($xmlarray);

/************************************************/
function getPostVar($var,$def="") {
    return isset($_POST[$var])?$_POST[$var]:$def;
}

/************************************************/
function XMLToArrayFlat($xml, &$return, $path='', $root=false) { 
    $children = array();
    if ($xml instanceof SimpleXMLElement) {
        $children = $xml->children();
        if ($root) { // we're at root
            $path .= '/'.$xml->getName();
        }
    }
    if (count($children) == 0){
        $return[$path] = (string)$xml;
        return;
    }
    $seen=array();
    foreach ($children as $child => $value) {
        $childname = ($child instanceof SimpleXMLElement)?$child->getName():$child;
        if (!isset($seen[$childname])){
            $seen[$childname]=0;
        }
        $seen[$childname]++;
        XMLToArrayFlat($value, $return, $path.'/'.$child.'['.$seen[$childname].']');
    }
}

/************************************************/
function tr($token, $def="") {
    global $language, $translation;
    if (strlen($language)==2 && file_exists("i18n/messages_${language}.properties")) {
        // OK
    } else {
        $language = "en";
    }
    if (!is_array($translation) || count($translation)<1) {
        // Load
        $translation = array();
        $handle = @fopen("i18n/messages_${language}.properties", "r");
        if ($handle) {
            $append = false;
            while (($buffer = fgets($handle, 4096)) !== false) {
                $buffer = trim($buffer);
                if ($append) {
                    if (substr($buffer,strlen($buffer)-1,1)!="\\") {
                        $append=false;
                    }
                    $translation[$key].= str_replace("\\","\n",$buffer);
                    continue;
                }
                $pos = strpos($buffer, "=");
                if ($pos!==false) {
                    $key = trim(substr($buffer,0,$pos));
                    $val = trim(substr($buffer,$pos+1));
                    if (substr($val,strlen($val)-1,1)=="\\") {
                        $append = true;
                    }
                    $translation[$key] = str_replace("\\","\n",$val);
                }
            }
            if (!feof($handle)) {
                //echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }
    }
    return array_key_exists($token,$translation)?$translation[$token]:($def==""?$token:$def);
}

?>