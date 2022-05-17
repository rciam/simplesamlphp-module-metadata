<?php

/**
 * Filter for copying the IdP tags to an attribute.
 *
 * This filter allows to copy any tags found in the IdP metadata to the
 * target attribute. If the attribute already exists, the values added
 * will be merged. If you instead want to replace the existing
 * attribute, you may add the '%replace' option.
 *
 * Example configuration in the config/config.php
 *
 *    authproc.aa = [
 *       ...
 *       '74' => [
 *            'class' => 'metaproc:IdpTag2Attribute',
 *             'targetAttribute' => 'urn:oid:1.3.6.1.4.1.25178.1.2.10'
 * 
 */
 
namespace SimpleSAML\Module\metaproc\Auth\Process;
use SimpleSAML\Logger;
use SimpleSAML\Metadata\MetaDataStorageHandler;

class IdpTag2Attribute extends \SimpleSAML\Auth\ProcessingFilter
{
    /**
     * Flag which indicates wheter this filter should append new values or replace old values.
     * @var bool
     */
    private $replace = false;

    private $targetAttribute = 'schacHomeOrganizationType';

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        assert('is_array($config)');

        foreach ($config as $name => $values) {
            if (is_int($name)) {
                if ($values === '%replace') {
                    $this->replace = true;
                } else {
                    throw new \Exception('Unknown flag: ' . var_export($values, true));
                }
                break;
            }
        }

        if (array_key_exists('targetAttribute', $config)) {
            if (!is_string($config['targetAttribute'])) {
                Logger::error(
                    "[IdpTag2Attribute] Configuration error: 'targetAttribute' is not a string"
                );
                throw new \Exception(
                    "IdpTag2Attribute configuration error: 'targetAttribute' is not a string"
                );
            } else {
                $this->targetAttribute = $config['targetAttribute'];
            }
        }
    }

    public function process(&$state)
    {
        try {
            assert('is_array($state)');
            $idpTags = $this->getIdPTags($this->getIdPMetadata($state));
            if (!empty($idpTags)) {
                if ($this->replace === false && !empty($state['Attributes'][$this->targetAttribute])) {
                    $state['Attributes'][$this->targetAttribute] = array_merge($state['Attributes'][$this->targetAttribute],$idpTags);
                } else {
                    $state['Attributes'][$this->targetAttribute] = $idpTags;
                }
                Logger::debug("[IdpTag2Attribute] process: targetAttribute array > " . var_export($state['Attributes'][$this->targetAttribute], true));
            }
        } catch (Error\Error $e) {
            $e->show();
        }
    }

    private function getIdPMetadata($state)
    {
        // If the module is active on a bridge,
        // $request['saml:sp:IdP'] will contain an entry id for the remote IdP.
        if (!empty($state['saml:sp:IdP'])) {
            $idpEntityId = $state['saml:sp:IdP'];
            return MetaDataStorageHandler::getMetadataHandler()->getMetaData($idpEntityId, 'saml20-idp-remote');
        } else {
            return $state['Source'];
        }
    }

    private function getIdPTags($idpMetadata)
    {
        if (!empty($idpMetadata['tags'])) {
            return $idpMetadata['tags'];
        }
        return [];
    }
}
