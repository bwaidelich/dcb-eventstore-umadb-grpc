# Test Certificates

This directory contains pre-generated self-signed certificates for testing TLS connections with UmaDB.

## Files

- `server-key.pem` - Private key for the test server
- `server-cert.pem` - Self-signed certificate (valid for 100 years)
- `openssl.cnf` - OpenSSL configuration used to generate the certificate

## Certificate Details

- **Common Name (CN)**: localhost
- **Subject Alternative Names (SANs)**:
  - DNS: localhost, *.localhost
  - IP: 127.0.0.1, 0.0.0.0
- **Validity**: 100 years from generation date

## Regenerating Certificates

If you need to regenerate these certificates:

```bash
cd tests/Integration/fixtures/certs
openssl genrsa -out server-key.pem 2048
openssl req -new -x509 -key server-key.pem -out server-cert.pem -days 36500 -config openssl.cnf
```

**Note**: These certificates are for testing purposes only and should never be used in production.
