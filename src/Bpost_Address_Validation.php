<?php
class Bpost_Address_Validation
{ 
  private function createJson(bool $structured = true, string $StreetName, ?string $StreetNumber = null, ?string $BoxNumber = null, ?int $PostalCode = null, ?string $MunicipalityName = null)
  {
      $request = array();
      $request['ValidateAddressesRequest'] = array(
        'AddressToValidateList' => array(
          'AddressToValidate' => array()
        ),
        'ValidateAddressOptions' => array(
          'IncludeNumberOfSuffixes' => TRUE,
          'IncludeDefaultGeoLocation' => TRUE,
          'IncludeFormatting' => TRUE,
          'IncludeSuggestions' => TRUE,
          'IncludeSubmittedAddress' => TRUE,
          'IncludeListOfBoxes' => TRUE,
          'IncludeNumberOfBoxes' => TRUE,
        ),
        'CallerIdentification' => array(
          'CallerName' => 'ici.Brussels'
        )
      );

      if($structured)  
      {
         $r = array(
              '@id' => '1',
              'PostalAddress' => array(
                'DeliveryPointLocation' => array(
                  'StructuredDeliveryPointLocation' => array(
                    'StreetName' => addslashes($StreetName),
                    'StreetNumber' => addslashes($StreetNumber),
                    'BoxNumber' => addslashes($BoxNumber),
                   )                  
                ),
                'PostalCodeMunicipality' => array(
                  'StructuredPostalCodeMunicipality' => array(
                    'PostalCode' => $PostalCode,
                    'MunicipalityName' => addslashes($MunicipalityName)
                  )
                )
              ),
              'DeliveringCountryISOCode' => 'BE',
              'DispatchingCountryISOCode' => 'BE'
            );    
      }
      else 
      {
        $r = array(
              '@id' => '1',
              'PostalAddress' => array(
                'DeliveryPointLocation' => array(
                  'UnstructuredDeliveryPointLocation' => addslashes($StreetName),
                   )                  
              ),
              'DeliveringCountryISOCode' => 'BE',
              'DispatchingCountryISOCode' => 'BE'
            );    
      };
 

      $request['ValidateAddressesRequest']['AddressToValidateList']['AddressToValidate'][] = $r;

      return $request;
  }

  private function doRequest($request)
  {
      $client         = new GuzzleHttp\Client();
      $response = $client->request('POST', 'https://webservices-pub.bpost.be/ws/ExternalMailingAddressProofingCSREST_v1/address/validateAddresses', [
        'json' => $request
    ]);
      

      $data = json_decode((string)$response->getBody());
      return $data->ValidateAddressesResponse->ValidatedAddressResultList->ValidatedAddressResult[0]->ValidatedAddressList->ValidatedAddress[0];
  }

  public function getAddress_Structured(string $StreetName, string $StreetNumber, ?string $BoxNumber, int $PostalCode, string $MunicipalityName)
  {
      $request        = $this->createJson(true, $StreetName, $StreetNumber, $BoxNumber, $PostalCode, $MunicipalityName);
      $this->_address = $this->doRequest($request);
  }

  public function getAddress_Unstructured(string $Address)
  {
      $request        = $this->createJson(false, $Address);
      $this->_address = $this->doRequest($request);
  }

  public function getNumberOfBoxes()
  {
      if(isset($this->_address->NumberOfBoxes))
        { return $this->_address->NumberOfBoxes; }
      else { return null; };
  }
  
  public function getMailBoxes()
  {
      if(isset($this->_address->NumberOfBoxes))
        { 
        	for ($i=0; $i<$this->_address->NumberOfBoxes; $i++)
        	{
        		$MailBoxes[] = $this->_address->ServicePointBoxList->ServicePointBoxResult[$i]->BoxNumber;
        	};
        	sort($MailBoxes, SORT_NATURAL);
        	return implode(", ", $MailBoxes);
        }
      else { return null; };
  }

  public function getAllAddress()
  {
      return $this->_address??null;
  }

  public function getLabelAddress(bool $lowercase = true)
  {
      if(isset($this->_address->Label->Line))
        {
          $data = $this->_address->Label->Line;
          $nb = count($data);
          for($i=0; $i<$nb; $i++)
          {
            $data[$i] = $lowercase?ucwords(mb_strtolower($data[$i])):$data[$i];
          }
          
          return $data;
        }
      else { return null; };

  }

  public function getStructuredAddress(bool $lowercase = true)
  {
      if(isset($this->_address->PostalAddress->StructuredDeliveryPointLocation->StreetName))
        {
          $data['StreetName'] = $this->_address->PostalAddress->StructuredDeliveryPointLocation->StreetName;
          $data['StreetNumber'] = $this->_address->PostalAddress->StructuredDeliveryPointLocation->StreetNumber;
          $data['BoxNumber'] = isset($this->_address->PostalAddress->StructuredDeliveryPointLocation->BoxNumber)?$this->_address->PostalAddress->StructuredDeliveryPointLocation->BoxNumber:"";
          $data['PostalCode'] = $this->_address->PostalAddress->StructuredPostalCodeMunicipality->PostalCode;
          $data['MunicipalityName'] = $this->_address->PostalAddress->StructuredPostalCodeMunicipality->MunicipalityName;
          $data['CountryName'] = $this->_address->PostalAddress->CountryName;
      
          if($lowercase) {
             $data['StreetName']        = ucwords(mb_strtolower($data['StreetName']));
             $data['MunicipalityName']  = ucwords(mb_strtolower($data['MunicipalityName']));
             $data['CountryName']       = ucwords(mb_strtolower($data['CountryName']));
          }

          return $data;
        }
      else { return null; };
  }

  public function getAddressLanguage()
  {
      return $this->_address->AddressLanguage??null;
  }

  public function getGeographicalLocation()
  {
      if(isset($this->_address->ServicePointDetail->GeographicalLocationInfo->GeographicalLocation->Latitude->Value))
      {
         $data['lat'] = $this->_address->ServicePointDetail->GeographicalLocationInfo->GeographicalLocation->Latitude->Value;
         $data['lon'] = $this->_address->ServicePointDetail->GeographicalLocationInfo->GeographicalLocation->Longitude->Value;
         return $data;
      }
      else { return null; };
  }

}
?>
