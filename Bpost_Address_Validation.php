<?php
class Bpost_Address_Validation
{
      
  public function __construct() {
    require_once '../vendor/autoload.php';
    }

  
  public function getAddress_Structurated(string $StreetName, string $StreetNumber, ?string $BoxNumber, int $PostalCode, string $MunicipalityName)
  {
      $client = new GuzzleHttp\Client();

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

      $request['ValidateAddressesRequest']['AddressToValidateList']['AddressToValidate'][] = $r;

      $response = $client->request('POST', 'https://webservices-pub.bpost.be/ws/ExternalMailingAddressProofingCSREST_v1/address/validateAddresses', [
        'json' => $request
    ]);
      

      $data = json_decode((string)$response->getBody());
      $this->_address = $data->ValidateAddressesResponse->ValidatedAddressResultList->ValidatedAddressResult[0]->ValidatedAddressList->ValidatedAddress[0];
  }

  public function getNumberOfBoxes()
  {
      if(isset($this->_address->NumberOfBoxes))
        { return $this->_address->NumberOfBoxes; }
      else { return null; };
  }

  public function getAllAddress()
  {
      return $this->_address;
  }

  public function getLabelAddress(bool $lowercase = true)
  {
      $data = $this->_address->Label->Line;
      $nb = count($data);
      for($i=0; $i<$nb; $i++)
      {
        $data[$i] = $lowercase?ucwords(mb_strtolower($data[$i])):$data[$i];
      }
      
      return $data;
  }

  public function getStructuredAddress(bool $lowercase = true)
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

  public function getAddressLanguage()
  {
      return $this->_address->AddressLanguage;
  }

  public function getGeographicalLocation()
  {
      $data['lat'] = $this->_address->ServicePointDetail->GeographicalLocationInfo->GeographicalLocation->Latitude->Value;
      $data['lon'] = $this->_address->ServicePointDetail->GeographicalLocationInfo->GeographicalLocation->Longitude->Value;
      return $data;
  }

}
?>