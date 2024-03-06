This module contains an example Webform configuration demonstrating integration with the Liberty Create Low-code API.

## Liberty Create API
When we think of APIs, we usually think of it to have a fixed data structure.  The Liberty Create API, however, does not refer to one or more APIs with fixed data structures.  Instead, API designers can create necessary APIs from a drag-and-drop UI.  This means, there is no common API spec that we can target for Drupal Webform integration.

In the rest of this README, we describe an **example** Liberty Create API for CRM case creation and then explain how API integration is achieved with Drupal Webform.

## Example API request
Let's look at an example REST API request first:
```
POST https://example-build.oncreate.app/api/REST/case_to_crm/0.1 HTTP/1.1
API-Authentication: [Token hidden]
API-Username: [Your username]
API-User-Token: [Token hidden]
Content-Type: application/json

{
    "payload":{
        "client_unique_identifier":"63105fe17248615689a02568ca95ff91",
        "function":"case_to_crm_create_update_case",
        "data":[
            {
                "source_system":"Some Text 1",
                "source_ref":"Some Text 2",
                "date_time_created":"25/12/2023 12:00",
                "resident_uprn":75,
                "case_uprn":75,
                "first_name":"Some Text 3",
                "last_name":"Some Text 4",
                "telephone_number_for_texts":"07700900000",
                "email_address":"nobody@example.com",
                "case_url":"http://www.example.com",
                "nature_of_enquiry":"Some Text 5",
                "disposal_date":"25/12/2023",
                "details":"Some Long Text 1",
                "documents":[
                    {
                        "file":{
                            "filename":"Example.txt",
                            "is_base64":true,
                            "content":"U29tZSBUZXh0IDE="
                        },
                        "filename":"Some Text 6",
                        "description":"Some Text 7"
                    }
                ]
            }
        ]
    }
}
```

## Example API response
What follows is an example response from the previous API call to create a CRM case.
```
{
    "payload":{
        "client_unique_identifier":"63105fe17248615689a02568ca95ff91",
        "result":"success",
        "error_code":null,
        "error_desc":null,
        "warnings":[
        ],
        "data":[
            {
                "result":"created",
                "error_code":null,
                "error_desc":null,
                "data":{
                    "source_system":"Some Text 1",
                    "source_ref":"Some Text 2",
                    "date_time_created":"1703505600",
                    "liberty_create_case_reference":"GE\/1"
                }
            }
        ]
    }
}
```

### Webform integration basics
So this is how we setup the integration:
- Under the "Settings > Emails/Handlers" tab of a Webform, add a "Remote post" Webform handler.  This handler comes bundled with the Webform module.
- Under the "General" tab of the handler configuration dialog, expand the "Completed" fieldset.
- In the "Completed URL" field, enter the API endpoint URL.  The example Webform config bundled with this module uses "https://example-build.oncreate.app/api/REST/case_to_crm/0.1" as this URL.  Adjust it accordingly for your target endpoint.
- In the "Completed custom data" field, map API fields with suitable Webform tokens.  Refer to the example config below to get a better idea.  This bit is in YAML format:
  ```
  payload:
      # client_unique_identifier is a required field.
      client_unique_identifier: "[webform:id]/[webform_submission:sid]"
      # "function" is also a required field.
      function: case_to_crm_create_update_case
      # So is "data".
      data:
        -
          # source_system is a required field.
          source_system: "Drupal Webforms" 
          # source_ref is a required field.
          source_ref: "[webform:id]/[webform_submission:sid]"
          # Everything else below is optional.
          date_time_created: "[webform_submission:completed:custom:d/m/Y H:i]"
          resident_uprn: "[webform_submission:values:residential_address:uprn]"
          case_uprn: "[webform_submission:values:case_address:uprn]"
          first_name: "[webform_submission:values:name:first]"
          last_name: "[webform_submission:values:name:last]"
          telephone_number_for_texts: "[webform_submission:values:phone]"
          email_address:  "[webform_submission:values:email]"
          case_url:  "[webform_submission:token-view-url]"
          nature_of_enquiry: "[webform:title]"
          disposal_date: "[webform_submission:purge_date:custom:d/m/Y:clear]"
          # For file fields, we use inline YAML syntax to avoid indentation issues.  This is because "file_details_for_liberty_create_api", our custom file token, gets replaced with several *other* tokens before the token value insertion starts.
          documents: ["[webform_submission:values:files:file_details_for_liberty_create_api]", "[webform_submission:values:more_files:file_details_for_liberty_create_api]"]
          #
          # Any other Webform submission token should be placed within the "details" field below.
          details: |-
            Case address: "[webform_submission:values:case_address:clear]"
            Residential address: "[webform_submission:values:residential_address:clear]"
            Details: "[webform_submission:values:details_of_enquiry:clear]"
  ```
  This assumes our Webform carries at least the following fields:
  - name
  - email
  - phone
  - residential_address
  - case_address
  - files
  - more_files
- Uncheck everything under the "Submission data" fieldset.
- Switch to the "Advanced" tab of the "Remote post" handler.
- Under "Additional settings", select "POST" from the "Method" dropdown.
- Select "JSON" from the "Post type" dropdown.
- Try the following snippet for "Custom options":
  ```
  headers:
    API-Authentication: "[env:DRUPAL_LIBERTY_CREATE_API_AUTH_KEY]"
    API-Username: "[env:DRUPAL_LIBERTY_CREATE_API_USERNAME]"
    API-User-Token: "[env:DRUPAL_LIBERTY_CREATE_API_USER_KEY]"
  ```
  This assumes that the [token_environment Drupal module](https://www.drupal.org/project/token_environment) is enabled and the following environment variables are present with their corresponding values:
  - DRUPAL_LIBERTY_CREATE_API_AUTH_KEY
  - DRUPAL_LIBERTY_CREATE_API_USERNAME
  - DRUPAL_LIBERTY_CREATE_API_USER_KEY

  Note that the token_environment module must be explicitly told that the above environment variables should be made available as Drupal tokens.  This is configured from "/admin/config/system/token-environment".
- The [webform_queued_post_handler Drupal module](https://packagist.org/packages/cyberwoven/webform_queued_post_handler) provides an alternate to the "Remote post" handler.  This handler is called "Async remote post" and uses Drupal's queue to manage API requests.  Items in Drupal's queue are usually processed during cron runs.  If you decide to use this handler instead, you may also find the [queue_ui](https://www.drupal.org/project/queue_ui) contrib module useful.

## Inspecting API responses
To inspect API responses, add the following "Value" type Webform elements to your Webform:
- "crm_response" whose value should be `[webform:handler:async_remote_post:completed:payload:result]; [webform:handler:async_remote_post:completed:payload:error_code]; [webform:handler:async_remote_post:completed:payload:error_desc]`.
- "crm_result" whose value should be `[webform:handler:async_remote_post:completed:payload:data:0:result]; [webform:handler:async_remote_post:completed:payload:data:0:error_code]; [webform:handler:async_remote_post:completed:payload:data:0:error_desc]`
- "crm_case_ref" whose value should be `[webform:handler:async_remote_post:completed:payload:data:0:data:0:liberty_create_case_reference]`.

This will ensure that API responses are stored alongside Webform submission values.  This makes it easier to inspect these from the "Results" tab of Webforms.

As you can see, the three above field values are using multiple tokens and these tokens are referring to "async_remote_post" which is the handler id.  Everything after ":completed:" in the token mirrors the API response.  Change all these if necessary.

## Summary
- Liberty Create APIs will vary from organisation to organisation.  There is no one-size fits all solution.  You can study the example Webform config provided with this module to get an idea about the integration process.
- Use the "Remote post" or "Async remote post" Webform handler to make HTTP POST requests to any Liberty Create REST API endpoint URLs.
- Use the "Completed custom data" settings of the above handlers to map Webform fields to REST API fields.
- Use the "Custom options" settings of the handlers to provide API authentication details.
- Use the crm_response, crm_result, and crm_case_ref *Value* type Webform fields to capture API responses.
