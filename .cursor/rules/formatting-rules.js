// WordPress Plugin Formatting Rules for PM Gym Management

// Detect this project as a WordPress plugin project
export const isWordPressPluginProject = (projectDir) => {
  return projectDir.endsWith('pm-gym');
};

// Define PHP formatting rules
export const rules = [
  // PHP formatting standards
  {
    name: 'PHP Indentation',
    description: 'Ensure consistent indentation in PHP files',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Check for mix of tabs and spaces
      if (content.includes('\t') && /^( {2,})/.test(content)) {
        return {
          message: 'Use consistent indentation (either tabs or spaces, not both)',
          severity: 'warning',
        };
      }
      return null;
    },
  },

  // PHP opening tags
  {
    name: 'PHP Opening Tags',
    description: 'Use <?php for PHP opening tags',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Check for short tags
      if (content.includes('<?=') || content.match(/<\?[^p]/)) {
        return {
          message: 'Use <?php instead of short tags',
          severity: 'warning',
        };
      }
      return null;
    },
  },

  // Yoda conditions
  {
    name: 'Yoda Conditions',
    description: 'Use Yoda conditions for comparisons',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Look for non-Yoda comparisons with variables on the left
      const nonYodaPattern = /if\s*\(\s*\$\w+\s*[=!]==?\s*['"][^'"]*['"]\s*\)/;
      if (nonYodaPattern.test(content)) {
        return {
          message: 'Use Yoda conditions (constants on the left of comparisons)',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // Brace style
  {
    name: 'Brace Style',
    description: 'Use same-line braces for control structures',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Look for control structures with braces on the next line
      const nextLineBracePattern = /if\s*\([^)]*\)\s*\n\s*{/;
      if (nextLineBracePattern.test(content)) {
        return {
          message: 'Opening braces should be on the same line as the control structure',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // Line length
  {
    name: 'Line Length',
    description: 'Keep lines reasonably short',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      const lines = content.split('\n');
      for (let i = 0; i < lines.length; i++) {
        if (lines[i].length > 100) {
          return {
            message: 'Consider keeping lines under 100 characters for readability',
            severity: 'info',
            line: i + 1,
          };
        }
      }
      return null;
    },
  },

  // WordPress naming conventions for variables
  {
    name: 'Variable Naming',
    description: 'Use snake_case for variable names',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Look for camelCase variables
      const camelCasePattern = /\$[a-z]+[A-Z][a-zA-Z0-9]*/;
      if (camelCasePattern.test(content)) {
        return {
          message: 'Use snake_case for variable names instead of camelCase',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // Space after control structures
  {
    name: 'Control Structure Spacing',
    description: 'Add a space after control structure keywords',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Look for control structures without a space
      const noSpacePattern = /(if|for|foreach|while|switch)\(/;
      if (noSpacePattern.test(content)) {
        return {
          message: 'Add a space between control structures and opening parenthesis',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // File endings
  {
    name: 'PHP File Endings',
    description: 'PHP files should end with a newline',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      if (!content.endsWith('\n')) {
        return {
          message: 'Files should end with a newline character',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // Comment formatting
  {
    name: 'Comment Formatting',
    description: 'Ensure proper comment formatting',
    files: ['**/*.php'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      // Look for comments without a space
      const noSpaceCommentPattern = /\/\/[^\s]/;
      if (noSpaceCommentPattern.test(content)) {
        return {
          message: 'Add a space after comment slashes',
          severity: 'info',
        };
      }
      return null;
    },
  },

  // Trailing whitespace
  {
    name: 'Trailing Whitespace',
    description: 'Remove trailing whitespace',
    files: ['**/*.php', '**/*.js', '**/*.css'],
    condition: isWordPressPluginProject,
    rule: (file) => {
      const content = file.content || '';
      const trailingWhitespacePattern = /[ \t]+$/m;
      if (trailingWhitespacePattern.test(content)) {
        return {
          message: 'Remove trailing whitespace',
          severity: 'info',
        };
      }
      return null;
    },
  }
]; 