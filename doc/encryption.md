## Encryption

Vault is using openssl to encrypt and decrypt the data.

By default, the `aes-256-cbc` method is used.

This method requires a `key` and an `iv`.

The key and iv are both derived from the user supplied vault password:

1. The user provided password is hashed using sha256 (this outputs 64 char hex string, encoding 32 bytes)
2. The first 32 characters (16 bytes) of the hash are used as the key
3. The last 32 characters (16 bytes) of the hash are used as the iv

Data encrypted using vault can be decrypted by calculating the key and iv using the method described above.
After this, you can use the openssl cli tools to decrypt the data:

    openssl enc  -aes-256-cbc -d -in vault/encrypted.filename -nosalt -nopad -K 55e98a1e353dbc084b0a14bc8b36e218 -iv 4ad71f006c9e4dca529ca5cc16472ed6

## Filenames

Filenames in the vault directory are encrypted in exactly the same way, but they are base64 encoded before saving to  disk, and any trailing `=` signs are removed (these are not needed for base64 decode to work).
