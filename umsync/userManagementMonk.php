<?php

class userManagementMonk {

    public $permissions;

    function __construct() {
        
    }

    function read() {
        global $xmlPath;
        $xml = '<users>';
        foreach (explode("\n", $this->fileGetContents(userPath)) as $line) {
            @list($username, $password) = explode("\t", $line);
            if (strlen($username) && strlen($password)) {
                $xml .= '<' . $username . '>';
                $xml .= '<password>' . $password . '</password>';
                if (is_file(sprintf($xmlPath, $username, '.disabled'))) {
                    $xml .= '<disabled>true</disabled>';
                    $xml .= $this->validXmlString($this->delPrefixSuffixSpaceXmlValues($this->delVersionFormatXml($this->fileGetContents(sprintf($xmlPath, $username, '.disabled')))));
                } else {
                    $xml .= '<disabled>false</disabled>';
                    $xml .= $this->validXmlString($this->delPrefixSuffixSpaceXmlValues($this->delVersionFormatXml($this->fileGetContents(sprintf($xmlPath, '', $username)))));
                }
                $xml .= '</' . $username . '>';
            }
        }
        $xml .= '</users>';
        $this->permissions = simplexml_load_string($xml);
    }

    function write() {
        global $xmlPath;
        foreach ($this->permissions as $k => $v) {
            $dom_permissions = new DOMDocument;
            $dom_permissions->preserveWhiteSpace = false;
            $dom_permissions->loadXML($this->addPrefixSuffixSpaceXmlValues($v->permissions->asXML()));
            $dom_permissions->formatOutput = true;
            if ((string) $v->disabled == 'true') {
                file_put_contents(sprintf($xmlPath, $k, '.disabled'), $this->delVersionFormatXml($dom_permissions->saveXML()));
                chmod(sprintf($xmlPath, $k, '.disabled'), 0660);
                file_put_contents(sprintf($xmlPath, $k, ''), '<permissions><global_permission> 1 </global_permission></permissions>');
                chmod(sprintf($xmlPath, $k, ''), 0660);
            } else {
                file_put_contents(sprintf($xmlPath, $k, ''), $this->delVersionFormatXml($dom_permissions->saveXML()));
                chmod(sprintf($xmlPath, $k, ''), 0660);
                @unlink(sprintf($xmlPath, $k, '.disabled'));
            }
        }
        $users_output = '';
        foreach ($this->permissions as $k => $v)
            $users_output .= $k . "\t" . $v->password . "\n";
        if (strlen($users_output) != 0) {
            file_put_contents(userPath, $users_output);
            chmod(userPath, 0660);
        }
    }

    function validXmlString($xml) {
        if (@simplexml_load_string($xml))
            return $xml;
        return '';
    }

    function fileGetContents($filename) {
        if (@$contents = file_get_contents($filename))
            return $contents;
        return '';
    }

    function delVersionFormatXml($xml) {
        $xml = preg_replace('/<\?xml.*\?>\n/', '', $xml);
        return preg_replace('/<\?xml.*\?>/', '', $xml);
    }

    function addPrefixSuffixSpaceXmlValues($xml) {
        return str_replace('>', '> ', str_replace('<', ' <', $xml));
    }

    function delPrefixSuffixSpaceXmlValues($xml) {
        return str_replace('> ', '>', str_replace(' <', '<', $xml));
    }

    function valid($username, $password) {
        if ($this->permissions->$username->password == $this->makemonkpw($password))
            return true;
        return false;
    }

    function makemonkpw($password) {
        exec('/target/gpfs2/monk/bin/makemonkpw -enc \'' . escapeshellarg($password) . '\'', $monkpw);
        if (isset($monkpw[0]))
            return $monkpw[0];
        else
            return $password;
    }

    function valid_username($username) {
        if (strlen($username) && preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $username) && substr(strtolower($username), 0, 3) != 'xml')
            return true;
        return false;
    }

    function add_username($target_username, $target_password) {
        if (!isset($this->permissions->$target_username) && strlen($target_password) && $this->valid_username($target_username)) {
            $this->permissions->$target_username->password = $this->makemonkpw($target_password);
            $this->permissions->$target_username->permissions->global_permission = '1';
            return true;
        }
        return false;
    }

    function delete_username($target_username) {
        global $xmlPath;
        if (isset($this->permissions->$target_username)) {
            unset($this->permissions->$target_username);
            if (is_file(sprintf($xmlPath, $target_username, '')))
                unlink(sprintf($xmlPath, $target_username, ''));
            return true;
        }
        return false;
    }

    function change_password($target_username, $target_password) {
        if (isset($this->permissions->$target_username) && strlen($target_password)) {
            $this->permissions->$target_username->password = $this->makemonkpw($target_password);
            return true;
        }
        return false;
    }

    function change_global_permission($target_username, $global_permission) {
        if (isset($this->permissions->$target_username->permissions->global_permission)) {
            $this->permissions->$target_username->permissions->global_permission = $global_permission;
            return true;
        }
        return false;
    }

    function add_book($target_username, $book_id, $book_permission, $page_from, $page_to) {
        if (isset($this->permissions->$target_username)) {
            foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/books/book/book_id') as $k => $v) {
                if ($v == $book_id) {
                    $this->permissions->$target_username->permissions->books->book[$k]->book_permission = $book_permission;
                    $this->permissions->$target_username->permissions->books->book[$k]->page_from = $page_from;
                    $this->permissions->$target_username->permissions->books->book[$k]->page_to = $page_to;
                    return true;
                }
            }
            $this->permissions->$target_username->permissions->books->book[]->book_id = $book_id;
            $count = count($this->permissions->$target_username->permissions->books->book) - 1;
            $this->permissions->$target_username->permissions->books->book[$count]->book_permission = $book_permission;
            $this->permissions->$target_username->permissions->books->book[$count]->page_from = $page_from;
            $this->permissions->$target_username->permissions->books->book[$count]->page_to = $page_to;
            return true;
        }
        return false;
    }

    function add_collection($target_username, $collection_id, $collection_permission) {
        if (isset($this->permissions->$target_username)) {
            foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/collections/collection/collection_id') as $k => $v) {
                if ($v == $collection_id) {
                    $this->permissions->$target_username->permissions->collections->collection[$k]->collection_permission = $collection_permission;
                    return true;
                }
            }
            $this->permissions->$target_username->permissions->collections->collection[]->collection_id = $collection_id;
            $count = count($this->permissions->$target_username->permissions->collections->collection) - 1;
            $this->permissions->$target_username->permissions->collections->collection[$count]->collection_permission = $collection_permission;
            return true;
        }
        return false;
    }

    function add_institution($target_username, $institution_id, $institution_permission) {
        if (isset($this->permissions->$target_username)) {
            foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/institutions/institution/institution_id') as $k => $v) {
                if ($v == $institution_id) {
                    $this->permissions->$target_username->permissions->institutions->institution[$k]->institution_permission = $institution_permission;
                    return true;
                }
            }
            $this->permissions->$target_username->permissions->institutions->institution[]->institution_id = $institution_id;
            $count = count($this->permissions->$target_username->permissions->institutions->institution) - 1;
            $this->permissions->$target_username->permissions->institutions->institution[$count]->institution_permission = $institution_permission;
            return true;
        }
        return false;
    }

    function delete_institution($target_username, $institution_id) {
        foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/institutions/institution/institution_id') as $k => $v) {
            if ($v == $institution_id) {
                unset($this->permissions->$target_username->permissions->institutions->institution[$k]);
                return true;
            }
        }
        return false;
    }

    function delete_collection($target_username, $collection_id) {
        foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/collections/collection/collection_id') as $k => $v) {
            if ($v == $collection_id) {
                unset($this->permissions->$target_username->permissions->collections->collection[$k]);
                return true;
            }
        }
        return false;
    }

    function delete_book($target_username, $book_id) {
        foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/books/book/book_id') as $k => $v) {
            if ($v == $book_id) {
                unset($this->permissions->$target_username->permissions->books->book[$k]);
                return true;
            }
        }
        return false;
    }

}

?>
