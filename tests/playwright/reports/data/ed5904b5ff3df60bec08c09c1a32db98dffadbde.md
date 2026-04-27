# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 00-auth.setup.ts >> authenticate customer and persist storage state
- Location: tests/playwright/00-auth.setup.ts:6:1

# Error details

```
Error: expect(received).toContain(expected) // indexOf

Expected value: 429
Received array: [302, 303]
```

# Page snapshot

```yaml
- generic [ref=e2]:
  - generic [ref=e3]:
    - generic [ref=e7]:
      - img [ref=e8]
      - generic [ref=e12]: Gratis ongkir untuk tiap pembelian pertama
    - banner [ref=e13]:
      - generic [ref=e16]:
        - link "ogitu logo ogitu Premium Store" [ref=e18] [cursor=pointer]:
          - /url: /
          - generic [ref=e19]:
            - img "ogitu logo" [ref=e21]
            - generic [ref=e22]:
              - paragraph [ref=e23]: ogitu
              - paragraph [ref=e24]: Premium Store
        - generic [ref=e26]:
          - generic [ref=e27]:
            - textbox "Cari produk, brand, kategori…" [ref=e28]
            - img [ref=e30]
          - generic [ref=e34]:
            - generic [ref=e35]: ⌘
            - generic [ref=e36]: K
        - generic [ref=e38]:
          - button "colorMode.switchToDark" [ref=e39]:
            - img [ref=e40]
          - separator [ref=e44]
          - link "Masuk" [ref=e46] [cursor=pointer]:
            - /url: /login
          - link "Daftar" [ref=e47] [cursor=pointer]:
            - /url: /register
      - navigation [ref=e48]:
        - generic [ref=e51]:
          - link "Beranda" [ref=e52] [cursor=pointer]:
            - /url: /
            - img [ref=e53]
            - text: Beranda
          - link "Toko" [ref=e57] [cursor=pointer]:
            - /url: /shop
            - img [ref=e58]
            - text: Toko
          - button "Kategori" [ref=e62]:
            - img [ref=e63]
            - text: Kategori
            - img [ref=e69]
          - link "Artikel" [ref=e71] [cursor=pointer]:
            - /url: /articles
            - img [ref=e72]
            - text: Artikel
    - main [ref=e76]:
      - generic [ref=e77]:
        - region "Promotional banners" [ref=e78]:
          - region [ref=e79]:
            - generic [ref=e81]:
              - tabpanel [ref=e82]:
                - generic [ref=e83]:
                  - img "Romantic & Warm" [ref=e85]
                  - generic [ref=e88]:
                    - generic [ref=e91]:
                      - generic [ref=e92]:
                        - generic [ref=e93]:
                          - img [ref=e94]
                          - text: Flash Sale
                        - generic [ref=e96]:
                          - generic [ref=e97]:
                            - img [ref=e98]
                            - text: Original
                          - generic [ref=e102]:
                            - img [ref=e103]
                            - text: Fast Delivery
                      - generic [ref=e108]:
                        - heading "Romantic & Warm" [level=2] [ref=e109]:
                          - text: Romantic &
                          - generic [ref=e110]: Warm
                        - paragraph [ref=e111]: Ada kekuatan tersembunyi di balik aroma kopi yang memikat
                      - generic [ref=e112]:
                        - link "Belanja Sekarang" [ref=e113] [cursor=pointer]:
                          - /url: /shop/Romantic%20&%20Warm
                          - text: Belanja Sekarang
                          - img [ref=e114]
                        - button "Copy promo code BIOLOVE" [ref=e116]:
                          - generic [ref=e117]:
                            - img [ref=e118]
                            - generic [ref=e122]: BIOLOVE
                          - generic [ref=e123]: Copy
                    - generic [ref=e125]:
                      - generic [ref=e127]: "01"
                      - generic [ref=e131]: Explore Items
              - tabpanel:
                - generic:
                  - generic:
                    - img "Portable Alkaline Ionizer Bottle"
                  - generic:
                    - generic:
                      - generic:
                        - generic:
                          - generic:
                            - generic:
                              - generic:
                                - generic:
                                  - img
                                  - text: Bundle Hemat
                                - generic:
                                  - generic:
                                    - img
                                    - text: Original
                                  - generic:
                                    - img
                                    - text: Fast Delivery
                              - generic:
                                - heading "Portable Alkaline Ionizer Bottle" [level=2]:
                                  - text: Portable Alkaline Ionizer
                                  - generic: Bottle
                                - paragraph: Transformasi Air Menjadi Energi Kehidupan
                              - generic:
                                - link "Belanja Sekarang":
                                  - /url: /shop/Alkaline%20Ionizer%20Bottle
                                  - text: Belanja Sekarang
                                  - img
                                - button "Copy promo code BIOALKALINE2026":
                                  - generic:
                                    - img
                                    - generic: BIOALKALINE2026
                                  - generic: Copy
                        - generic:
                          - generic:
                            - generic:
                              - generic: "01"
                            - generic: Explore Items
              - tabpanel:
                - generic:
                  - generic:
                    - img "Herbal Nyeri Sendi"
                  - generic:
                    - generic:
                      - generic:
                        - generic:
                          - generic:
                            - generic:
                              - generic:
                                - generic:
                                  - img
                                  - text: Bundle Hemat
                                - generic:
                                  - generic:
                                    - img
                                    - text: Original
                                  - generic:
                                    - img
                                    - text: Fast Delivery
                              - generic:
                                - heading "Herbal Nyeri Sendi" [level=2]:
                                  - text: Herbal Nyeri
                                  - generic: Sendi
                                - paragraph: Rahasia Nusantara untuk Kesehatan Saraf
                              - generic:
                                - link "Belanja Sekarang":
                                  - /url: /shop/rahasia-nusantara-untuk-kesehatan-saraf
                                  - text: Belanja Sekarang
                                  - img
                                - button "Copy promo code BIOZENERVE2026":
                                  - generic:
                                    - img
                                    - generic: BIOZENERVE2026
                                  - generic: Copy
                        - generic:
                          - generic:
                            - generic:
                              - generic: "01"
                            - generic: Explore Items
              - tabpanel:
                - generic:
                  - generic:
                    - img "Sihir Kesegaran Alami"
                  - generic:
                    - generic:
                      - generic:
                        - generic:
                          - generic:
                            - generic:
                              - generic:
                                - generic:
                                  - img
                                  - text: Flash Sale
                                - generic:
                                  - generic:
                                    - img
                                    - text: Original
                                  - generic:
                                    - img
                                    - text: Fast Delivery
                              - generic:
                                - heading "Sihir Kesegaran Alami" [level=2]:
                                  - text: Sihir Kesegaran
                                  - generic: Alami
                                - paragraph: Rasakan Sihir Kesegaran dari Dunia Fantasi
                              - generic:
                                - link "Belanja Sekarang":
                                  - /url: /shop
                                  - text: Belanja Sekarang
                                  - img
                                - button "Copy promo code ogitu2025":
                                  - generic:
                                    - img
                                    - generic: ogitu2025
                                  - generic: Copy
                        - generic:
                          - generic:
                            - generic:
                              - generic: "01"
                            - generic: Explore Items
              - tabpanel:
                - generic:
                  - generic:
                    - img "The Secret"
                  - generic:
                    - generic:
                      - generic:
                        - generic:
                          - generic:
                            - generic:
                              - generic:
                                - generic:
                                  - img
                                  - text: Diskon Spesial
                                - generic:
                                  - generic:
                                    - img
                                    - text: Original
                                  - generic:
                                    - img
                                    - text: Fast Delivery
                              - generic:
                                - heading "The Secret" [level=2]:
                                  - text: The
                                  - generic: Secret
                                - paragraph: Intimate & Glow Secret Package
                              - generic:
                                - link "Belanja Sekarang":
                                  - /url: /shop/the-secretThe%20Secret
                                  - text: Belanja Sekarang
                                  - img
                                - button "Copy promo code THEINTIMATE":
                                  - generic:
                                    - img
                                    - generic: THEINTIMATE
                                  - generic: Copy
                        - generic:
                          - generic:
                            - generic:
                              - generic: "01"
                            - generic: Explore Items
            - generic:
              - generic:
                - button "carousel.prev" [ref=e132]:
                  - img [ref=e133]
                - button "carousel.next" [ref=e135]:
                  - img [ref=e136]
              - tablist "carousel.dots" [ref=e138]:
                - tab "carousel.goto" [selected] [ref=e139] [cursor=pointer]
                - tab "carousel.goto" [ref=e140] [cursor=pointer]
                - tab "carousel.goto" [ref=e141] [cursor=pointer]
                - tab "carousel.goto" [ref=e142] [cursor=pointer]
                - tab "carousel.goto" [ref=e143] [cursor=pointer]
        - generic [ref=e146]:
          - generic [ref=e147]:
            - img [ref=e149]
            - generic [ref=e154]:
              - heading "Gratis Ongkir" [level=3] [ref=e155]
              - paragraph [ref=e156]: Untuk pembelian di atas Rp 150k
          - generic [ref=e157]:
            - img [ref=e159]
            - generic [ref=e163]:
              - heading "Pembayaran Aman" [level=3] [ref=e164]
              - paragraph [ref=e165]: Transaksi terenkripsi 100%
          - generic [ref=e166]:
            - img [ref=e168]
            - generic [ref=e172]:
              - heading "Support 24/7" [level=3] [ref=e173]
              - paragraph [ref=e174]: Tim kami siap membantu Anda
          - generic [ref=e175]:
            - img [ref=e177]
            - generic [ref=e181]:
              - heading "Easy Returns" [level=3] [ref=e182]
              - paragraph [ref=e183]: Pengembalian gratis 30 hari
        - generic [ref=e184]:
          - generic [ref=e186]:
            - generic [ref=e187]:
              - generic [ref=e188]:
                - generic [ref=e189]:
                  - img [ref=e190]
                  - text: Semua Kategori
                - heading "Belanja Berdasarkan Kategori" [level=2] [ref=e196]
                - paragraph [ref=e197]: Temukan produk favorit dari berbagai kategori pilihan
              - link "Lihat Semua" [ref=e198] [cursor=pointer]:
                - /url: /shop
                - text: Lihat Semua
                - img [ref=e199]
            - generic [ref=e201]:
              - link "Herba Care Herba Care 6 produk Lihat" [ref=e202] [cursor=pointer]:
                - /url: /shop?category=herba-care
                - img "Herba Care" [ref=e203]
                - generic [ref=e205]:
                  - paragraph [ref=e206]: Herba Care
                  - generic [ref=e207]:
                    - img [ref=e208]
                    - text: 6 produk
                - generic [ref=e210]:
                  - text: Lihat
                  - img [ref=e211]
              - link "Beauty Care Beauty Care 6 produk Lihat" [ref=e213] [cursor=pointer]:
                - /url: /shop?category=Beauty-care
                - img "Beauty Care" [ref=e214]
                - generic [ref=e216]:
                  - paragraph [ref=e217]: Beauty Care
                  - generic [ref=e218]:
                    - img [ref=e219]
                    - text: 6 produk
                - generic [ref=e221]:
                  - text: Lihat
                  - img [ref=e222]
              - link "Health Therapy Health Therapy 6 produk Lihat" [ref=e224] [cursor=pointer]:
                - /url: /shop?category=health-therapy
                - img "Health Therapy" [ref=e225]
                - generic [ref=e227]:
                  - paragraph [ref=e228]: Health Therapy
                  - generic [ref=e229]:
                    - img [ref=e230]
                    - text: 6 produk
                - generic [ref=e232]:
                  - text: Lihat
                  - img [ref=e233]
              - link "Fashion Fashion 3 produk Lihat" [ref=e235] [cursor=pointer]:
                - /url: /shop?category=fashion
                - img "Fashion" [ref=e236]
                - generic [ref=e238]:
                  - paragraph [ref=e239]: Fashion
                  - generic [ref=e240]:
                    - img [ref=e241]
                    - text: 3 produk
                - generic [ref=e243]:
                  - text: Lihat
                  - img [ref=e244]
              - link "Electronic & Gadgets Electronic & Gadgets 2 produk Lihat" [ref=e246] [cursor=pointer]:
                - /url: /shop?category=electronic-gadgets
                - img "Electronic & Gadgets" [ref=e247]
                - generic [ref=e249]:
                  - paragraph [ref=e250]: Electronic & Gadgets
                  - generic [ref=e251]:
                    - img [ref=e252]
                    - text: 2 produk
                - generic [ref=e254]:
                  - text: Lihat
                  - img [ref=e255]
              - link "Smart Living Smart Living 1 produk Lihat" [ref=e257] [cursor=pointer]:
                - /url: /shop?category=smart-living
                - img "Smart Living" [ref=e258]
                - generic [ref=e260]:
                  - paragraph [ref=e261]: Smart Living
                  - generic [ref=e262]:
                    - img [ref=e263]
                    - text: 1 produk
                - generic [ref=e265]:
                  - text: Lihat
                  - img [ref=e266]
          - generic [ref=e271]:
            - generic [ref=e272]:
              - generic [ref=e273]:
                - generic [ref=e274]:
                  - img [ref=e275]
                  - text: Terlaris Bulan Ini
                - heading "Produk Unggulan" [level=2] [ref=e279]
                - paragraph [ref=e280]: Produk terlaris pilihan pelanggan kami
              - link "Lihat Semua" [ref=e281] [cursor=pointer]:
                - /url: /shop
                - text: Lihat Semua
                - img [ref=e282]
            - generic [ref=e284]:
              - generic [ref=e286]:
                - link "BIOZENERVE - (Pre_Order) Terlaris Wishlist" [ref=e287] [cursor=pointer]:
                  - /url: /shop/biozenerve
                  - img "BIOZENERVE - (Pre_Order)" [ref=e288]
                  - generic [ref=e290]: Terlaris
                  - button "Wishlist" [ref=e291]:
                    - img [ref=e292]
                - generic [ref=e294]:
                  - generic [ref=e295]:
                    - generic [ref=e296]:
                      - img [ref=e297]
                      - generic [ref=e299]: "0.0"
                    - generic [ref=e300]: 12 terjual
                  - link "BIOZENERVE - (Pre_Order)" [ref=e301] [cursor=pointer]:
                    - /url: /shop/biozenerve
                    - heading "BIOZENERVE - (Pre_Order)" [level=3] [ref=e302]
                  - generic [ref=e303]:
                    - paragraph [ref=e305]: Rp 350.000
                    - button [ref=e306]:
                      - img [ref=e307]
              - generic [ref=e313]:
                - link "BIOLOVE Coffee , 1 Kotak 10 Sct @20gr - (Pre_Order) Hot Wishlist" [ref=e314] [cursor=pointer]:
                  - /url: /shop/biolove
                  - img "BIOLOVE Coffee , 1 Kotak 10 Sct @20gr - (Pre_Order)" [ref=e315]
                  - generic [ref=e317]: Hot
                  - button "Wishlist" [ref=e318]:
                    - img [ref=e319]
                - generic [ref=e321]:
                  - generic [ref=e322]:
                    - generic [ref=e323]:
                      - img [ref=e324]
                      - generic [ref=e326]: "0.0"
                    - generic [ref=e327]: 10 terjual
                  - link "BIOLOVE Coffee , 1 Kotak 10 Sct @20gr - (Pre_Order)" [ref=e328] [cursor=pointer]:
                    - /url: /shop/biolove
                    - heading "BIOLOVE Coffee , 1 Kotak 10 Sct @20gr - (Pre_Order)" [level=3] [ref=e329]
                  - generic [ref=e330]:
                    - paragraph [ref=e332]: Rp 350.000
                    - button [ref=e333]:
                      - img [ref=e334]
              - generic [ref=e340]:
                - link "INTIMATE & GLOW SECRETS PACKAGE (Paket 8 Pcs) Hot Wishlist" [ref=e341] [cursor=pointer]:
                  - /url: /shop/intimate-glow-screts-package
                  - img "INTIMATE & GLOW SECRETS PACKAGE (Paket 8 Pcs)" [ref=e342]
                  - generic [ref=e344]: Hot
                  - button "Wishlist" [ref=e345]:
                    - img [ref=e346]
                - generic [ref=e348]:
                  - generic [ref=e349]:
                    - generic [ref=e350]:
                      - img [ref=e351]
                      - generic [ref=e353]: "0.0"
                    - generic [ref=e354]: 7 terjual
                  - link "INTIMATE & GLOW SECRETS PACKAGE (Paket 8 Pcs)" [ref=e355] [cursor=pointer]:
                    - /url: /shop/intimate-glow-screts-package
                    - heading "INTIMATE & GLOW SECRETS PACKAGE (Paket 8 Pcs)" [level=3] [ref=e356]
                  - generic [ref=e357]:
                    - paragraph [ref=e359]: Rp 350.000
                    - button [ref=e360]:
                      - img [ref=e361]
              - generic [ref=e367]:
                - link "BIOZENLITE - 1 Kotak, 8 Sct @15gr Wishlist" [ref=e368] [cursor=pointer]:
                  - /url: /shop/biozenlite
                  - img "BIOZENLITE - 1 Kotak, 8 Sct @15gr" [ref=e369]
                  - button "Wishlist" [ref=e370]:
                    - img [ref=e371]
                - generic [ref=e373]:
                  - generic [ref=e374]:
                    - generic [ref=e375]:
                      - img [ref=e376]
                      - generic [ref=e378]: "0.0"
                    - generic [ref=e379]: 4 terjual
                  - link "BIOZENLITE - 1 Kotak, 8 Sct @15gr" [ref=e380] [cursor=pointer]:
                    - /url: /shop/biozenlite
                    - heading "BIOZENLITE - 1 Kotak, 8 Sct @15gr" [level=3] [ref=e381]
                  - generic [ref=e382]:
                    - paragraph [ref=e384]: Rp 350.000
                    - button [ref=e385]:
                      - img [ref=e386]
              - generic [ref=e392]:
                - link "BIOZENION PENDANT Green Wishlist" [ref=e393] [cursor=pointer]:
                  - /url: /shop/biozenion-pendant-green
                  - img "BIOZENION PENDANT Green" [ref=e394]
                  - button "Wishlist" [ref=e395]:
                    - img [ref=e396]
                - generic [ref=e398]:
                  - generic [ref=e400]:
                    - img [ref=e401]
                    - generic [ref=e403]: "0.0"
                  - link "BIOZENION PENDANT Green" [ref=e404] [cursor=pointer]:
                    - /url: /shop/biozenion-pendant-green
                    - heading "BIOZENION PENDANT Green" [level=3] [ref=e405]
                  - generic [ref=e406]:
                    - paragraph [ref=e408]: Rp 1.500.000
                    - button [ref=e409]:
                      - img [ref=e410]
              - generic [ref=e416]:
                - link "BIOZENION PENDANT Blue Wishlist" [ref=e417] [cursor=pointer]:
                  - /url: /shop/biozenion-pendant-blue
                  - img "BIOZENION PENDANT Blue" [ref=e418]
                  - button "Wishlist" [ref=e419]:
                    - img [ref=e420]
                - generic [ref=e422]:
                  - generic [ref=e424]:
                    - img [ref=e425]
                    - generic [ref=e427]: "0.0"
                  - link "BIOZENION PENDANT Blue" [ref=e428] [cursor=pointer]:
                    - /url: /shop/biozenion-pendant-blue
                    - heading "BIOZENION PENDANT Blue" [level=3] [ref=e429]
                  - generic [ref=e430]:
                    - paragraph [ref=e432]: Rp 1.500.000
                    - button [ref=e433]:
                      - img [ref=e434]
              - generic [ref=e440]:
                - link "BIOALKALINE BOTTLE Wishlist" [ref=e441] [cursor=pointer]:
                  - /url: /shop/bioalkaline-bottle
                  - img "BIOALKALINE BOTTLE" [ref=e442]
                  - button "Wishlist" [ref=e443]:
                    - img [ref=e444]
                - generic [ref=e446]:
                  - generic [ref=e448]:
                    - img [ref=e449]
                    - generic [ref=e451]: "5.0"
                  - link "BIOALKALINE BOTTLE" [ref=e452] [cursor=pointer]:
                    - /url: /shop/bioalkaline-bottle
                    - heading "BIOALKALINE BOTTLE" [level=3] [ref=e453]
                  - generic [ref=e454]:
                    - paragraph [ref=e456]: Rp 1.500.000
                    - button [ref=e457]:
                      - img [ref=e458]
              - generic [ref=e464]:
                - link "ogitu INTIMATE WASH ( 1 Btl 60ml) (RETAIL PLAN !) Wishlist" [ref=e465] [cursor=pointer]:
                  - /url: /shop/ogitu-intimate-wash
                  - img "ogitu INTIMATE WASH ( 1 Btl 60ml) (RETAIL PLAN !)" [ref=e466]
                  - button "Wishlist" [ref=e467]:
                    - img [ref=e468]
                - generic [ref=e470]:
                  - generic [ref=e472]:
                    - img [ref=e473]
                    - generic [ref=e475]: "0.0"
                  - link "ogitu INTIMATE WASH ( 1 Btl 60ml) (RETAIL PLAN !)" [ref=e476] [cursor=pointer]:
                    - /url: /shop/ogitu-intimate-wash
                    - heading "ogitu INTIMATE WASH ( 1 Btl 60ml) (RETAIL PLAN !)" [level=3] [ref=e477]
                  - generic [ref=e478]:
                    - paragraph [ref=e480]: Rp 100.000
                    - button [ref=e481]:
                      - img [ref=e482]
              - generic [ref=e488]:
                - link "ogitu BEAUTY SOAP (3 pcs) (RETAIL PLAN !) Wishlist" [ref=e489] [cursor=pointer]:
                  - /url: /shop/ogitu-beauty-soap
                  - img "ogitu BEAUTY SOAP (3 pcs) (RETAIL PLAN !)" [ref=e490]
                  - button "Wishlist" [ref=e491]:
                    - img [ref=e492]
                - generic [ref=e494]:
                  - generic [ref=e496]:
                    - img [ref=e497]
                    - generic [ref=e499]: "0.0"
                  - link "ogitu BEAUTY SOAP (3 pcs) (RETAIL PLAN !)" [ref=e500] [cursor=pointer]:
                    - /url: /shop/ogitu-beauty-soap
                    - heading "ogitu BEAUTY SOAP (3 pcs) (RETAIL PLAN !)" [level=3] [ref=e501]
                  - generic [ref=e502]:
                    - paragraph [ref=e504]: Rp 100.000
                    - button [ref=e505]:
                      - img [ref=e506]
              - generic [ref=e512]:
                - link "Feminine Spray Vanilla & Buble Gum (4 Btl @10 ml) (RETAIL PLAN) Wishlist" [ref=e513] [cursor=pointer]:
                  - /url: /shop/secrets-feminine-spray-vanilla-buble-gum
                  - img "Feminine Spray Vanilla & Buble Gum (4 Btl @10 ml) (RETAIL PLAN)" [ref=e514]
                  - button "Wishlist" [ref=e515]:
                    - img [ref=e516]
                - generic [ref=e518]:
                  - generic [ref=e520]:
                    - img [ref=e521]
                    - generic [ref=e523]: "0.0"
                  - link "Feminine Spray Vanilla & Buble Gum (4 Btl @10 ml) (RETAIL PLAN)" [ref=e524] [cursor=pointer]:
                    - /url: /shop/secrets-feminine-spray-vanilla-buble-gum
                    - heading "Feminine Spray Vanilla & Buble Gum (4 Btl @10 ml) (RETAIL PLAN)" [level=3] [ref=e525]
                  - generic [ref=e526]:
                    - paragraph [ref=e528]: Rp 200.000
                    - button [ref=e529]:
                      - img [ref=e530]
              - generic [ref=e536]:
                - link "ogitu POLO SHIRT, Logo Bordir, Hitam Berkerah Lengan Panjang Wishlist" [ref=e537] [cursor=pointer]:
                  - /url: /shop/ogitu-polo-shirt-hitam-berkerah-lengan-panjang
                  - img "ogitu POLO SHIRT, Logo Bordir, Hitam Berkerah Lengan Panjang" [ref=e538]
                  - button "Wishlist" [ref=e539]:
                    - img [ref=e540]
                - generic [ref=e542]:
                  - generic [ref=e544]:
                    - img [ref=e545]
                    - generic [ref=e547]: "0.0"
                  - link "ogitu POLO SHIRT, Logo Bordir, Hitam Berkerah Lengan Panjang" [ref=e548] [cursor=pointer]:
                    - /url: /shop/ogitu-polo-shirt-hitam-berkerah-lengan-panjang
                    - heading "ogitu POLO SHIRT, Logo Bordir, Hitam Berkerah Lengan Panjang" [level=3] [ref=e549]
                  - generic [ref=e550]:
                    - paragraph [ref=e552]: Rp 250.000
                    - button [ref=e553]:
                      - img [ref=e554]
              - generic [ref=e560]:
                - link "ogitu Terahertz Blower Wishlist" [ref=e561] [cursor=pointer]:
                  - /url: /shop/TERAHERTZBLOWER
                  - img "ogitu Terahertz Blower" [ref=e562]
                  - button "Wishlist" [ref=e563]:
                    - img [ref=e564]
                - generic [ref=e566]:
                  - generic [ref=e568]:
                    - img [ref=e569]
                    - generic [ref=e571]: "0.0"
                  - link "ogitu Terahertz Blower" [ref=e572] [cursor=pointer]:
                    - /url: /shop/TERAHERTZBLOWER
                    - heading "ogitu Terahertz Blower" [level=3] [ref=e573]
                  - generic [ref=e574]:
                    - paragraph [ref=e576]: Rp 2.000.000
                    - button [ref=e577]:
                      - img [ref=e578]
        - generic [ref=e586]:
          - generic [ref=e587]:
            - generic [ref=e588]:
              - img [ref=e589]
              - text: Koleksi Eksklusif & Premium
            - heading "Kesehatan & Kecantikan Tanpa Batas" [level=2] [ref=e593]:
              - text: Kesehatan & Kecantikan
              - text: Tanpa Batas
            - paragraph [ref=e594]: Masuk ke ekosistem wellness kami. Temukan produk revolusioner yang dirancang khusus untuk meningkatkan kualitas hidup Anda.
            - generic [ref=e595]:
              - generic [ref=e596]:
                - img [ref=e597]
                - heading "Eksklusif" [level=3] [ref=e601]
                - paragraph [ref=e602]: Produk premium terakurasi
              - generic [ref=e603]:
                - img [ref=e604]
                - heading "Hemat" [level=3] [ref=e609]
                - paragraph [ref=e610]: Diskon member hingga 30%
              - generic [ref=e611]:
                - img [ref=e612]
                - heading "Terbatas" [level=3] [ref=e616]
                - paragraph [ref=e617]: Penawaran kilat mingguan
            - generic [ref=e618]:
              - link "Jelajahi Produk" [ref=e619] [cursor=pointer]:
                - /url: /shop
                - text: Jelajahi Produk
                - img [ref=e620]
              - button "Konsultasi Gratis" [ref=e622]
          - generic [ref=e624]:
            - generic [ref=e626]:
              - img [ref=e628]
              - generic [ref=e632]:
                - paragraph [ref=e633]: Terverifikasi
                - paragraph [ref=e634]: BPOM & Halal
            - generic [ref=e636]:
              - img [ref=e638]
              - generic [ref=e642]:
                - paragraph [ref=e643]: Terlaris
                - paragraph [ref=e644]: 100K+ Terjual
        - generic [ref=e651]:
          - generic [ref=e652]:
            - generic [ref=e654]:
              - generic [ref=e655]:
                - generic [ref=e656]:
                  - img [ref=e658]
                  - heading "Penghasilan Tanpa Batas" [level=3] [ref=e662]
                - paragraph [ref=e663]: Dapatkan profit retail dan bonus jaringan setiap hari. Sistem bagi hasil yang transparan dan otomatis masuk ke wallet Anda.
              - generic [ref=e664]:
                - img [ref=e665]
                - paragraph [ref=e669]: 75%
                - paragraph [ref=e670]: Payout Ratio
              - generic [ref=e671]:
                - img [ref=e672]
                - paragraph [ref=e676]: 100+
                - paragraph [ref=e677]: Kota Terjangkau
            - generic [ref=e678]:
              - img [ref=e680]
              - generic [ref=e685]:
                - paragraph [ref=e686]: Target BV Reward
                - paragraph [ref=e687]: Raih Expander 2025
          - generic [ref=e689]:
            - paragraph [ref=e690]: Entrepreneurship Program
            - heading "Bangun Kerajaan Bisnis Anda Sendiri" [level=2] [ref=e691]:
              - text: Bangun Kerajaan
              - text: Bisnis Anda Sendiri
            - paragraph [ref=e692]: Bukan sekadar belanja, ini adalah peluang kemitraan. Manfaatkan sistem pemasaran jaringan kami yang sudah teruji untuk meraih kebebasan finansial dan waktu.
            - generic [ref=e693]:
              - generic [ref=e694]:
                - img [ref=e696]
                - generic [ref=e698]:
                  - heading "Komisi Langsung - 20%" [level=4] [ref=e699]
                  - paragraph [ref=e700]: Komisi retail langsung terhitung otomatis setiap transaksi tervalidasi.
              - generic [ref=e701]:
                - img [ref=e703]
                - generic [ref=e708]:
                  - heading "Bonus Jaringan - Unlimited" [level=4] [ref=e709]
                  - paragraph [ref=e710]: Bangun jaringan mitra tanpa batas wilayah dengan perhitungan bonus real-time.
              - generic [ref=e711]:
                - img [ref=e713]
                - generic [ref=e717]:
                  - heading "Reward Mewah - Umroh/Mobil" [level=4] [ref=e718]
                  - paragraph [ref=e719]: Capai milestone penjualan untuk membuka reward eksklusif bertingkat.
            - link "Gabung Sekarang" [ref=e721] [cursor=pointer]:
              - /url: /register
            - paragraph [ref=e722]: "* Syarat dan ketentuan berlaku. BV (Business Volume) dihitung otomatis per transaksi."
        - generic [ref=e724]:
          - generic [ref=e725]:
            - heading "Kata Pelanggan Kami" [level=2] [ref=e726]
            - paragraph [ref=e727]: Ribuan pelanggan puas berbelanja bersama kami
          - region [ref=e728]:
            - group [ref=e731]:
              - generic [ref=e732]:
                - generic [ref=e733]:
                  - img [ref=e734]
                  - img [ref=e736]
                  - img [ref=e738]
                  - img [ref=e740]
                  - img [ref=e742]
                - paragraph [ref=e744]: "\"Sangat bermanfaat, semenjak saya menggunakan Botol ini, keluhan asam lambung dan perih diperut saya sudah tidak ada lagi\""
                - generic [ref=e745]:
                  - generic [ref=e746]: A
                  - generic [ref=e747]:
                    - paragraph [ref=e748]: ALFI KASANAH
                    - paragraph [ref=e749]: BIOALKALINE BOTTLE
    - contentinfo [ref=e750]:
      - generic [ref=e753]:
        - generic [ref=e754]:
          - img [ref=e756]
          - generic [ref=e760]:
            - paragraph [ref=e761]: 100% Aman
            - paragraph [ref=e762]: Transaksi terenkripsi
        - generic [ref=e763]:
          - img [ref=e765]
          - generic [ref=e770]:
            - paragraph [ref=e771]: Gratis Ongkir
            - paragraph [ref=e772]: Min. belanja Rp 499K
        - generic [ref=e773]:
          - img [ref=e775]
          - generic [ref=e780]:
            - paragraph [ref=e781]: Easy Return
            - paragraph [ref=e782]: 30 hari pengembalian
        - generic [ref=e783]:
          - img [ref=e785]
          - generic [ref=e787]:
            - paragraph [ref=e788]: Support 24/7
            - paragraph [ref=e789]: Siap membantu Anda
      - generic [ref=e790]:
        - generic [ref=e792]:
          - generic [ref=e793]:
            - generic [ref=e794]:
              - img "ogitu logo" [ref=e796]
              - generic [ref=e797]: ogitu
            - paragraph [ref=e798]: "ogitu.id: Web Store E-commerce Unggulan dari PT. Zenith SInergi Utama"
            - generic [ref=e799]:
              - paragraph [ref=e800]: Dapatkan promo terbaru
              - paragraph [ref=e801]: Diskon eksklusif langsung ke inbox Anda
              - generic [ref=e802]:
                - generic [ref=e803]:
                  - textbox "Alamat email" [ref=e804]
                  - img [ref=e806]
                - button "Subscribe" [ref=e810]: Langganan
            - generic [ref=e811]:
              - link "Facebook" [ref=e812] [cursor=pointer]:
                - /url: https://www.facebook.com/share/18MBEaatKL
                - img [ref=e813]
              - link "Instagram" [ref=e815] [cursor=pointer]:
                - /url: https://www.instagram.com/ogitu.id?igsh=Z25lNGh3bWU0ZXc2
                - img [ref=e816]
              - link "WhatsApp" [ref=e820] [cursor=pointer]:
                - /url: https://wa.me/628989972227
                - img [ref=e821]
          - generic [ref=e823]:
            - generic [ref=e824]:
              - generic [ref=e825]:
                - heading "Informasi" [level=3] [ref=e827]
                - list [ref=e829]:
                  - listitem [ref=e830]:
                    - link "Info Produk Nutrisi & Herbal" [ref=e831] [cursor=pointer]:
                      - /url: /page/infoproduknutrisiherbal
                  - listitem [ref=e832]:
                    - link "Bioalkaline & Biozenion" [ref=e833] [cursor=pointer]:
                      - /url: /page/botolalkali
              - generic [ref=e834]:
                - heading "Informasi" [level=3] [ref=e836]
                - list [ref=e838]:
                  - listitem [ref=e839]:
                    - link "Intimate Secret & Glow Package" [ref=e840] [cursor=pointer]:
                      - /url: /page/intimatesecret
                  - listitem [ref=e841]:
                    - link "Tentang Kami" [ref=e842] [cursor=pointer]:
                      - /url: /page/about
            - generic [ref=e844]:
              - generic [ref=e845]:
                - img [ref=e847]
                - generic [ref=e851]:
                  - paragraph [ref=e852]: Email Support
                  - paragraph [ref=e853]: ogituid@gmail.com
              - generic [ref=e854]:
                - img [ref=e856]
                - generic [ref=e858]:
                  - paragraph [ref=e859]: Hubungi Kami
                  - paragraph [ref=e860]: "+628989972227"
        - generic [ref=e861]:
          - generic [ref=e862]:
            - paragraph [ref=e863]: Metode Pembayaran
            - generic [ref=e864]:
              - generic [ref=e865]:
                - img [ref=e866]
                - generic [ref=e869]: Online Payement
              - generic [ref=e870]:
                - img [ref=e871]
                - generic [ref=e874]: Ewallet
          - generic [ref=e876]:
            - link "Hubungi Kami" [ref=e877] [cursor=pointer]:
              - /url: /page/contact
            - link "FAQ - Pertanyaan Umum" [ref=e878] [cursor=pointer]:
              - /url: /page/faq
            - link "Syarat & Ketentuan" [ref=e879] [cursor=pointer]:
              - /url: /page/terms
            - link "Kebijakan Privasi" [ref=e880] [cursor=pointer]:
              - /url: /page/privacy
          - generic [ref=e881]:
            - paragraph [ref=e882]: © 2026 ogitu. All rights reserved.
            - generic [ref=e883]:
              - generic [ref=e884]:
                - img [ref=e885]
                - generic [ref=e889]: Transaksi aman & terenkripsi
              - separator [ref=e890]
              - generic [ref=e892]:
                - img [ref=e893]
                - generic [ref=e897]: SSL Secured
  - region "Notifications (F8)":
    - list
  - region "Notifications (F8)":
    - list
```

# Test source

```ts
  1  | import { expect, Page, Response } from '@playwright/test'
  2  | 
  3  | export const TEST_CUSTOMER = {
  4  |     username: 'ogitu1',
  5  |     password: 'ogitu@2026',
  6  |     email: 'zenithsinergiutama@gmail.com',
  7  | }
  8  | 
  9  | export const TEST_PRODUCT_SLUG = 'biozenion-pendant-green'
  10 | export const CUSTOMER_STORAGE_STATE = 'tests/playwright/.auth/customer.json'
  11 | 
  12 | async function retryDelayFromResponse(response: Response | null): Promise<number> {
  13 |     if (!response) {
  14 |         return 3
  15 |     }
  16 | 
  17 |     const headers = await response.allHeaders()
  18 | 
  19 |     return Number(headers['retry-after'] ?? 3)
  20 | }
  21 | 
  22 | export async function gotoAuthPage(page: Page, path: string, attempts = 3): Promise<Response | null> {
  23 |     let response: Response | null = null
  24 | 
  25 |     for (let attempt = 1; attempt <= attempts; attempt++) {
  26 |         response = await page.goto(path, { waitUntil: 'domcontentloaded' })
  27 | 
  28 |         if (response?.status() !== 429) {
  29 |             return response
  30 |         }
  31 | 
  32 |         await page.waitForTimeout((await retryDelayFromResponse(response) + 1) * 1000)
  33 |     }
  34 | 
  35 |     return response
  36 | }
  37 | 
  38 | export async function createCustomerSession(page: Page, username: string, password: string): Promise<void> {
  39 |     await page.goto('/', { waitUntil: 'domcontentloaded' })
  40 | 
  41 |     const csrfToken = await page.locator('meta[name="csrf-token"]').getAttribute('content')
  42 | 
  43 |     expect(csrfToken).toBeTruthy()
  44 | 
  45 |     const response = await page.request.post('/login', {
  46 |         form: {
  47 |             _token: csrfToken ?? '',
  48 |             username,
  49 |             password,
  50 |         },
  51 |         maxRedirects: 0,
  52 |     })
  53 | 
> 54 |     expect([302, 303]).toContain(response.status())
     |                        ^ Error: expect(received).toContain(expected) // indexOf
  55 | }
  56 | 
  57 | /**
  58 |  * Login via the storefront login page using username + password.
  59 |  * Targets "Masuk ke Akun" button to avoid ambiguity with newsletter subscribe.
  60 |  */
  61 | export async function loginAs(
  62 |     page: Page,
  63 |     username: string,
  64 |     password: string,
  65 |     options: { assumeOnLoginPage?: boolean } = {},
  66 | ): Promise<void> {
  67 |     if (!options.assumeOnLoginPage) {
  68 |         const response = await gotoAuthPage(page, '/login')
  69 | 
  70 |         expect(response?.status()).toBe(200)
  71 |     }
  72 | 
  73 |     const usernameInput = page.locator('input[autocomplete="username"], input[placeholder*="username" i]').first()
  74 | 
  75 |     await expect(usernameInput).toBeVisible({ timeout: 10_000 })
  76 | 
  77 |     await usernameInput.fill(username)
  78 |     await page.locator('#password').fill(password)
  79 |     await page.getByRole('button', { name: /Masuk ke Akun/i }).click()
  80 | 
  81 |     await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20_000 })
  82 | }
  83 | 
```
