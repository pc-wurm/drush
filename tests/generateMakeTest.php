<?php

/**
 * Generate makefile tests
 *
 * @group commands
 * @group make
 */
class generateMakeCase extends Drush_CommandTestCase {
  function testGenerateMake() {
    $sites = $this->setUpDrupal(1, TRUE, '7', 'standard');

    $options = array(
      'yes' => NULL,
      'pipe' => NULL,
      'root' => $this->webroot(),
      'uri' => key($sites),
      'cache' => NULL,
      'invoke' => NULL, // Don't validate options
    );
    $this->drush('pm-download', array('omega', 'context', 'delta', 'adaptive_image', 'ctools'), $options);
    $this->drush('pm-enable', array('omega', 'context', 'delta', 'adaptive_image'), $options);

    $makefile = UNISH_SANDBOX . '/dev.make';

    // First generate a simple makefile with no version information
    $this->drush('generate-makefile', array($makefile), array('exclude-versions' => NULL) + $options);
    $expected = '; This file was auto-generated by drush make
core = 7.x

api = 2
projects[] = "drupal"
; Modules
projects[] = "adaptive_image"
projects[] = "ctools"
projects[] = "context"
projects[] = "delta"
; Themes
projects[] = "omega"';
    $actual = trim(file_get_contents($makefile));

    $this->assertEquals($expected, $actual);

    // Download a module to a 'contrib' directory to test the subdir feature
    mkdir($this->webroot() + '/sites/all/modules/contrib');
    $this->drush('pm-download', array('views'), array('destination' => 'sites/all/modules/contrib') + $options);
    $this->drush('pm-enable', array('views'), $options);
    $this->drush('generate-makefile', array($makefile), array('exclude-versions' => NULL) + $options);
    $expected = '; This file was auto-generated by drush make
core = 7.x

api = 2
projects[] = "drupal"
; Modules
projects[] = "adaptive_image"
projects[] = "ctools"
projects[] = "context"
projects[] = "delta"
projects[views][subdir] = "contrib"

; Themes
projects[] = "omega"';
    $actual = trim(file_get_contents($makefile));

    $this->assertEquals($expected, $actual);

    // Generate a makefile with version numbers.
    $this->drush('generate-makefile', array($makefile), $options);
    $actual = file_get_contents($makefile);
    $this->assertContains('projects[adaptive_image][version] = "', $actual);
  }
}
