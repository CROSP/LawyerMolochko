#!/usr/bin/env node

// Quick test script to verify MCP connection works
const https = require('https');

const WP_URL = 'https://lawyermolochko.ddev.site:8443';
const WP_USER = 'admin';
const WP_PASS = 'VQyDBhUyvbZYgGgo7KBQH69i';

const auth = Buffer.from(`${WP_USER}:${WP_PASS}`).toString('base64');

console.log('🧪 Testing WordPress REST API connection...\n');

const options = {
  hostname: 'lawyermolochko.ddev.site',
  port: 8443,
  path: '/wp-json/wp/v2/pages/222',
  method: 'GET',
  headers: {
    'Authorization': `Basic ${auth}`,
    'Accept': 'application/json'
  },
  rejectUnauthorized: false // For self-signed certificates
};

const req = https.request(options, (res) => {
  let data = '';
  
  res.on('data', (chunk) => {
    data += chunk;
  });
  
  res.on('end', () => {
    if (res.statusCode === 200) {
      const page = JSON.parse(data);
      console.log('✅ Connection successful!');
      console.log(`📄 Page ID: ${page.id}`);
      console.log(`📝 Title: ${page.title?.rendered || 'N/A'}`);
      console.log(`🔗 Slug: ${page.slug || 'N/A'}`);
      console.log('\n✅ WordPress REST API is working correctly!');
      console.log('✅ MCP should work after Cursor restart.');
    } else {
      console.error(`❌ Error: HTTP ${res.statusCode}`);
      console.error(data);
    }
  });
});

req.on('error', (error) => {
  console.error('❌ Connection error:', error.message);
});

req.end();
