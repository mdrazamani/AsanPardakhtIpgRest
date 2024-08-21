#!/bin/bash

VERSION=$(cat VERSION)

IFS='.' read -r -a VERSION_PARTS <<< "$VERSION"

PATCH=${VERSION_PARTS[2]}
MINOR=${VERSION_PARTS[1]}
MAJOR=${VERSION_PARTS[0]}

PATCH=$((PATCH + 1))

if [ "$PATCH" -ge 16 ]; then
    PATCH=0
    MINOR=$((MINOR + 1))
fi

if [ "$MINOR" -ge 16 ]; then
    MINOR=0
    MAJOR=$((MAJOR + 1))
fi

NEW_VERSION="$MAJOR.$MINOR.$PATCH"

echo "$NEW_VERSION" > VERSION

echo "Bumping version to $NEW_VERSION"
