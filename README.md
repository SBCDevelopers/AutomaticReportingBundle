# AutomaticReportingBundle

Send daily PDF reports about the application's activity.

# Installation

1. `composer require sbc/auto-reporting-bundle`<br>
2. Enable the bundle in AppKernel.php `new SBC\AutomaticReportingBundle\AutomaticReportingBundle(),`<br>

# Usage
### Step 1:
Set the configuration:<br>
```
# AutomaticReportingBundle configuration
automatic_reporting:
    app_name:   'Your applciation name' # will be displayed in the email
    recipients: ['mail1@site.com', 'mail2@site.com'] # multiple recipients
```
### Step 2:
Call `@Report` Annotation in the entities you want to follow:
```php
/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 * @Report(
 *  dateColumn="creationDate",
 *  reportingName="Poste"
 * )
 */
class Post
{
...
}
```

### Step3
* Call `php bin/console reporting:generate` to build and send the report
* Or create a cron job that trigger this command every day

# What will actually happen ?
After calling the command the bundle will parse and get all the entities with the `@Report` annotation and
count all the rows that being created in the current day using the `dateColumn` attribute,
after that the bundle will generate and send a PDF attachment to the recipients containing the summary using the `reportingName` attribute for each entity.