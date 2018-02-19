# Information about the assets inside `test/assets` folder

## list-of-blobs.xml
Contains a sample as of '2018-02-15' downloaded and formatted from
https://cfdisat.blob.core.windows.net/lco?restype=container&comp=list&prefix=l_RFC_2018_02_11

## Data samples
The file `datasample-contents.txt` is a random extract from real data of only 100 records

The file `datasample-signed.txt` is a the previous file but with smime using sample
SAT certificates converted to PEM:
```
openssl smime -sign -nodetach -text -in datasample-contents.txt -out datasample-signed.txt \
        -outform der -signer CSD01_AAA010101AAA.cer.pem -inkey CSD01_AAA010101AAA.key.pem
``` 

The file `datasample-signed.txt.gz` is just the previous file but with `gzip`

The file `datasample-contents-2.txt` is almost equal to `datasample-contents.txt` but with the following changes:
```
    CEGM940425861|0|0 => CEGM940425861|1|0
    CERC7911221X1|0|0 => CERC7911221X1|1|1
    CIAR800128CJ1|0|0 => CIAR800128CJ1|0|1
    CIFM750811MFA|0|0 => (removed)
    (inserted)        => CAXX800101PJ9|0|0
```

## Certificate and private key

`CSD01_AAA010101AAA.cer.pem` & `CSD01_AAA010101AAA.key.pem` are sample files provided by SAT converted to PEM.
They are used to smime sign the data sample.
