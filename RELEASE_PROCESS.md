# KaelyAuth Release Process

## 🚀 Release Workflow

### After making changes and committing:

```bash
# 1. Commit your changes
git add .
git commit -m "feat: fix web authentication and add proper error handling"

# 2. Update version tag (if needed)
git tag -d v1.0.0
git push origin :refs/tags/v1.0.0
git tag -a v1.0.0 -m "Release v1.0.0 - Stable web authentication with proper error handling"
git push origin v1.0.0
```

### Complete Release Process:

```bash
# 1. Make your changes
# 2. Test thoroughly
# 3. Update CHANGELOG.md if needed
# 4. Commit changes
git add .
git commit -m "feat: your feature description"

# 5. Update version tag
git tag -d v1.0.0
git push origin :refs/tags/v1.0.0
git tag -a v1.0.0 -m "Release v1.0.0 - Your release description"
git push origin v1.0.0

# 6. Verify tag was pushed
git tag -l
git ls-remote --tags origin
```

## 📋 Version Management

### Current Version: v1.0.0

### Version History:
- **v1.0.0**: Initial stable release
  - Web authentication with proper error handling
  - WebAuthController implementation
  - Fixed $errors variable issue
  - Complete UI components (Blade)
  - ServiceProvider registration fixes

### Next Release Planning:
- **v1.1.0**: Planned features
  - Livewire UI components
  - Additional OAuth providers
  - Enhanced audit logging

## 🔧 Quick Commands Reference

```bash
# Remove local tag
git tag -d v1.0.0

# Remove remote tag
git push origin :refs/tags/v1.0.0

# Create new tag
git tag -a v1.0.0 -m "Release v1.0.0 - Description"

# Push tag to remote
git push origin v1.0.0

# List all tags
git tag -l

# Check remote tags
git ls-remote --tags origin
```

## 📝 Release Checklist

- [ ] All changes committed
- [ ] Tests passing
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Version tag created and pushed
- [ ] Release notes prepared
- [ ] GitHub release created (if applicable)

## 🎯 Release Notes Template

```markdown
# Release v1.0.0

## 🚀 New Features
- Web authentication system
- Proper error handling for Blade views
- Complete UI components

## 🐛 Bug Fixes
- Fixed "Undefined variable $errors" issue
- Resolved KaelyAuthManager dependency injection
- Fixed web routes configuration

## 🔧 Improvements
- Added WebAuthController for web authentication
- Updated ServiceProvider registration
- Enhanced README with installation instructions

## 📚 Documentation
- Updated README with VCS repository instructions
- Added troubleshooting section
- Complete installation guide
```

## ⚡ Quick Release Script

Create a script for faster releases:

```bash
#!/bin/bash
# release.sh

VERSION=$1
MESSAGE=$2

if [ -z "$VERSION" ] || [ -z "$MESSAGE" ]; then
    echo "Usage: ./release.sh <version> <message>"
    echo "Example: ./release.sh v1.0.0 'Release v1.0.0 - Stable web authentication'"
    exit 1
fi

echo "Releasing $VERSION..."

# Remove old tag
git tag -d $VERSION
git push origin :refs/tags/$VERSION

# Create new tag
git tag -a $VERSION -m "$MESSAGE"
git push origin $VERSION

echo "Release $VERSION completed!"
```

Usage:
```bash
chmod +x release.sh
./release.sh v1.0.0 "Release v1.0.0 - Stable web authentication"
``` 