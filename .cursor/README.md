# PM Gym Management - Cursor Rules

This directory contains custom rules and settings for Cursor IDE when working with the PM Gym Management WordPress plugin.

## Rules Overview

The rules are divided into two main categories:

1. **WordPress Plugin Rules** (`.cursor/rules/wordpress-plugin-rules.js`)

   - Code organization and WordPress best practices
   - Security checks for WordPress functions
   - Database query safety rules
   - Proper use of WordPress hooks and functions
   - Code snippets for common WordPress patterns

2. **Formatting Rules** (`.cursor/rules/formatting-rules.js`)
   - PHP code style and formatting rules
   - WordPress coding standards enforcement
   - Consistent indentation and whitespace rules

## Features

- **Code Snippets**: Pre-configured snippets for common WordPress plugin patterns
- **Auto-completions**: Suggestions for WordPress-specific functions and naming conventions
- **Linting Rules**: Custom rules for WordPress plugin development
- **Security Checks**: Rules to help prevent common WordPress security issues

## Usage

Cursor will automatically load these rules when opening the project. You'll see:

- Inline warnings and suggestions based on the rule definitions
- Code snippets available when typing in PHP files
- Auto-completion suggestions for WordPress-specific functions
- Code formatting recommendations

## Rule Severity Levels

Rules use the following severity levels:

- **Error**: Critical issues that should be fixed immediately
- **Warning**: Important issues that should be addressed
- **Info**: Suggestions for best practices

## Settings

The `.cursor/settings.json` file configures Cursor IDE with optimal settings for WordPress plugin development:

- 4 space indentation
- Format on save
- PHP-specific settings
- Rule sets enablement

## Customization

To customize these rules:

1. Edit the appropriate rules file in `.cursor/rules/`
2. Modify the settings in `.cursor/settings.json`

## WordPress Coding Standards

These rules aim to align with the official [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) and best practices for plugin development.
