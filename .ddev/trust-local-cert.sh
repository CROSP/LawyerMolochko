#!/bin/bash
# Trust the mkcert CA so https://lawyer-molochko.com.ua:8443 shows no certificate error.
# Run this once. For Firefox: no sudo. For Chrome: run with sudo or import CA manually.

set -e
CAROOT=$(mkcert -CAROOT)
echo "mkcert CA root: $CAROOT"
echo ""

# Try NSS (Firefox) first - no sudo needed if libnss3-tools is installed
if command -v certutil &>/dev/null; then
  echo "Installing CA into NSS (Firefox)..."
  export TRUST_STORES=nss
  mkcert -install 2>/dev/null && echo "Firefox: CA installed. Restart Firefox." || true
fi

# System store (Chrome, curl) - needs sudo
echo "Installing CA into system store (for Chrome, system-wide)..."
if mkcert -install 2>/dev/null; then
  echo "System: CA installed."
else
  echo "Run with sudo for Chrome: sudo $0"
  echo "Or import manually in Chrome: Settings → Privacy → Security → Manage certificates → Authorities → Import → $CAROOT/rootCA.pem"
fi
echo ""
echo "Then restart your browser and reload https://lawyer-molochko.com.ua:8443"
