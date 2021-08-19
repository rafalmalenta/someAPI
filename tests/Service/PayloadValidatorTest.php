<?php

namespace App\Tests\Service;

use App\Services\PayloadValidator;
use PHPUnit\Framework\TestCase;

class PayloadValidatorTest extends TestCase
{
    public function testItCorrectlyValidateJSON()
    {
        $validjson = "{\"test2\":\"das f d fin\",\"test\":\"jhgjg\"}";
        $validator = new PayloadValidator();
        $this->assertSame(true,$validator->isRequestValidJson($validjson));
        $invalidjson = "{\"comment\":\"dasda!!!!@@@@admfg fd gfd gdf ggg fdg dhtrn\"\"trask\": \"jhgjg\"}";
        $validator = new PayloadValidator();
        $this->assertSame(false,$validator->isRequestValidJson($invalidjson));
    }

   public function testItCorrectlyChecksIfFieldsExist()
   {
       $requiredFields = ["email","password"];
       $validjson = "{\"email\":\"someemail\",\"password\":\"supersecretpasword\"}";
       $validator = new PayloadValidator();
       $validator->isRequestValidJson($validjson);
       $array = $validator->getRequestContent();
       $value = $validator->allRequiredFieldsPassed($requiredFields);

       $this->assertSame($array['email'],'someemail');
       $this->assertTrue($value);

       $requiredFields = ["email","password"];
       $validjson = "{\"emasil\":\"someemail\",\"password\":\"supersecretpasword\"}";
       $validator = new PayloadValidator();
       $validator->isRequestValidJson($validjson);
       $array = $validator->getRequestContent();
       $value = $validator->allRequiredFieldsPassed($requiredFields);

       $this->assertFalse($value);

   }

}
