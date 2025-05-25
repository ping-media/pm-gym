// WordPress Plugin Development Rules for PM Gym Management

// Detect this project as a WordPress plugin project
export const isWordPressPluginProject = (projectDir) => {
  return projectDir.endsWith('pm-gym');
};

// Define PHP file defaults
export const rules = [
  // File organization rules
  {
    name: 'WordPress Plugin Structure',
    description: 'Maintain standard WordPress plugin directory structure',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      // Encourage proper file organization
      if (file.path.includes('/admin/') && !file.path.includes('class-')) {
        return {
          message: 'Admin PHP class files should follow the format class-{name}.php',
          severity: 'warning',
        };
      }
      if (file.path.includes('/includes/') && !file.path.includes('class-')) {
        return {
          message: 'Include PHP class files should follow the format class-{name}.php',
          severity: 'warning',
        };
      }
      return null;
    },
  },

  // PHP coding standards
  {
    name: 'PHP Class Documentation',
    description: 'Ensure PHP classes have proper documentation',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if (content.includes('class ') && !content.includes('/**')) {
        return {
          message: 'PHP classes should have proper PHPDoc comments',
          severity: 'warning',
        };
      }
      return null;
    },
  },

  // WordPress hook naming conventions
  {
    name: 'WordPress Hook Prefix',
    description: 'Use pm_gym prefix for hooks',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if ((content.includes('add_action(') || content.includes('add_filter(')) && 
          !content.includes('pm_gym_') && !content.includes('admin_') && !content.includes('wp_')) {
        return {
          message: 'Custom hooks should use the pm_gym_ prefix',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // Database table naming
  {
    name: 'Database Table Constants',
    description: 'Use PM_GYM_ prefix for database table constants',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if (content.includes('define(') && content.includes('TABLE') && !content.includes('PM_GYM_')) {
        return {
          message: 'Database table constants should use the PM_GYM_ prefix',
          severity: 'warning',
        };
      }
      return null;
    },
  },

  // Security checks
  {
    name: 'WordPress Security',
    description: 'Enforce WordPress security best practices',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if (content.includes('$_POST[') && !content.includes('sanitize_') && !content.includes('wp_verify_nonce')) {
        return {
          message: 'Always sanitize $_POST data and verify nonces',
          severity: 'error',
        };
      }
      return null;
    },
  },

  // WordPress internationalization
  {
    name: 'Internationalization',
    description: 'Use translation functions for user-facing strings',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Look for string patterns that might need translation
      const hasTextStrings = /['"]([A-Z][a-z].{10,})['"]/.test(content);
      const hasTranslations = content.includes('__(' || content.includes('_e(') || content.includes('esc_html__');
      
      if (hasTextStrings && !hasTranslations) {
        return {
          message: 'User-facing strings should be translatable with __() or esc_html__()',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // CSS and JS file organization
  {
    name: 'Asset Organization',
    description: 'Keep assets in the correct directories',
    files: ['**/*.css', '**/*.js'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      if (file.path.endsWith('.css') && !file.path.includes('/css/')) {
        return {
          message: 'CSS files should be in a css directory',
          severity: 'warning',
        };
      }
      if (file.path.endsWith('.js') && !file.path.includes('/js/')) {
        return {
          message: 'JavaScript files should be in a js directory',
          severity: 'warning',
        };
      }
      return null;
    },
  },

  // WordPress data escaping
  {
    name: 'Data Escaping',
    description: 'Ensure proper data escaping',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if (content.includes('echo') && 
          !content.includes('esc_html') && 
          !content.includes('esc_attr') && 
          !content.includes('esc_url') &&
          !content.includes('wp_kses')) {
        return {
          message: 'Always escape output with esc_html(), esc_attr(), or esc_url()',
          severity: 'warning',
        };
      }
      return null;
    },
  },

  // Database queries
  {
    name: 'Database Queries',
    description: 'Use $wpdb->prepare for SQL queries',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if (content.includes('$wpdb->query') && !content.includes('$wpdb->prepare')) {
        return {
          message: 'Use $wpdb->prepare to safely handle database queries',
          severity: 'error',
        };
      }
      return null;
    },
  },

  // Enqueue scripts and styles
  {
    name: 'Asset Enqueuing',
    description: 'Properly enqueue scripts and styles',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if ((content.includes('<script') || content.includes('<style')) && 
          file.path !== 'uninstall.php') {
        return {
          message: 'Use wp_enqueue_script and wp_enqueue_style instead of inline tags',
          severity: 'warning',
        };
      }
      return null;
    },
  }
];

// Code snippets for common WordPress plugin patterns
export const snippets = [
  {
    name: 'WordPress Plugin Header',
    description: 'Standard WordPress plugin header',
    condition: isWordPressPluginProject,
    files: ['*.php'],
    body: `/**
 * Plugin Name: PM Gym Management
 * Plugin URI: https://wpexpertdeep.com/pm-gym
 * Description: A comprehensive gym management system for WordPress with member management, attendance tracking, and fee management.
 * Version: 1.0.0
 * Author: Deep Goyal
 * Author URI: https://wpexpertdeep.com
 * Text Domain: pm-gym
 * Domain Path: /languages
 */`,
  },
  {
    name: 'WordPress AJAX Handler',
    description: 'Standard WordPress AJAX handler',
    condition: isWordPressPluginProject,
    files: ['**/*.php'],
    body: `add_action('wp_ajax_pm_gym_${1:action_name}', 'pm_gym_${1:action_name}_handler');
add_action('wp_ajax_nopriv_pm_gym_${1:action_name}', 'pm_gym_${1:action_name}_handler');

function pm_gym_${1:action_name}_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pm_gym_${1:action_name}_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Get and sanitize data
    $data = isset($_POST['data']) ? sanitize_text_field($_POST['data']) : '';
    
    // Process data
    ${2:// Add your code here}
    
    // Send response
    wp_send_json_success('${3:Success message}');
}`,
  },
  {
    name: 'WordPress Shortcode',
    description: 'Standard WordPress shortcode',
    condition: isWordPressPluginProject,
    files: ['**/*.php'],
    body: `/**
 * Shortcode: [pm_gym_${1:shortcode_name}]
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function pm_gym_${1:shortcode_name}_shortcode($atts) {
    // Extract and default attributes
    $atts = shortcode_atts(
        array(
            '${2:attr}' => '${3:default}',
        ),
        $atts,
        'pm_gym_${1:shortcode_name}'
    );
    
    // Start output buffering
    ob_start();
    
    // Include template
    include PM_GYM_PLUGIN_DIR . 'public/partials/${4:template-file}.php';
    
    // Return buffered content
    return ob_get_clean();
}
add_shortcode('pm_gym_${1:shortcode_name}', 'pm_gym_${1:shortcode_name}_shortcode');`,
  },
  {
    name: 'WordPress Database Query',
    description: 'Safe WordPress database query',
    condition: isWordPressPluginProject,
    files: ['**/*.php'],
    body: `global $wpdb;
$table_name = PM_GYM_${1:TABLE_NAME};

// Prepared statement for safe querying
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM %s WHERE ${2:column} = %s",
        $table_name,
        $${3:variable}
    )
);

if (!empty($results)) {
    foreach ($results as $row) {
        // Process each row
        ${4:// Your code here}
    }
}`,
  }
];

// Auto-completions for WordPress-specific functions
export const autoCompletions = [
  {
    name: 'pm_gym_prefix',
    description: 'Use the pm_gym_ prefix for custom functions',
    condition: isWordPressPluginProject,
    files: ['**/*.php'],
    match: /function\s+([^_].*?)\(/,
    suggest: (match) => {
      if (!match[1].startsWith('pm_gym_')) {
        return {
          message: 'Consider using the pm_gym_ prefix for custom functions',
          replacement: `function pm_gym_${match[1]}(`
        };
      }
      return null;
    }
  }
]; 