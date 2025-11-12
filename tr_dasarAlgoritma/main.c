#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <stdarg.h>

#ifdef _WIN32
    #include <windows.h>
    #include <conio.h>
    #define CLEAR_SCREEN "cls"
    #define SLEEP(ms) Sleep(ms)
    #define GETCH() _getch()
#else
    #include <unistd.h>
    #include <termios.h>
    #define CLEAR_SCREEN "clear"
    #define SLEEP(ms) usleep(ms * 1000)
    #define GETCH() getchar()
#endif

#define MAX 100

typedef struct {
    int hari;
    int bulan;
    int tahun;
} Tanggal;

typedef struct Roti {
    int kode;
    char nama[50];
    int stok;
    int harga;
    Tanggal tanggalPembuatan;
    struct Roti *next;
} Roti;

typedef struct {
    int kode;
    int harga;
    char nama[50];
    int qty;
} Pesanan;

Roti *head = NULL;
char history[100][100];
int jumlahHistory = 0;
char pesan[100];

// Fungsi untuk membuat node baru
// Roti* itu pointer ke struct Roti
Roti* createNode(int kode, char nama[], int stok, int harga, Tanggal tanggal) {
    Roti *newNode = (Roti*)malloc(sizeof(Roti));
    if (newNode == NULL) {
        printf("❌ Gagal mengalokasi memori!\n");
        return NULL;
    }

    newNode->kode = kode;
    strcpy(newNode->nama, nama);
    newNode->stok = stok;
    newNode->harga = harga;
    newNode->tanggalPembuatan = tanggal;
    newNode->next = NULL;

    return newNode;
}

// Fungsi untuk generate data awal
void generate10Roti() {
    Tanggal t1 = {1, 6, 2024};
    Roti *newNode1 = createNode(1, "Aoka", 20, 12000, t1);
    insertAtEnd(&head, newNode1);
}

// Fungsi untuk menambah node di akhir linked list
void insertAtEnd(Roti **head, Roti *newNode) {
    if (*head == NULL) {
        *head = newNode;
        return;
    }

    Roti *current = *head;
    while (current->next != NULL) {
        current = current->next;
    }
    current->next = newNode;
}

// Fungsi untuk menghitung jumlah data
int countData(Roti *head) {
    int count = 0;
    Roti *current = head;
    while (current != NULL) {
        count++;
        current = current->next;
    }
    return count;
}

// Fungsi untuk mencari node berdasarkan kode
Roti* findNodeByKode(Roti *head, int kode) {
    Roti *current = head;
    while (current != NULL) {
        if (current->kode == kode) {
            return current;
        }
        current = current->next;
    }
    return NULL;
}

// Fungsi untuk menghapus node berdasarkan kode
void deleteNodeByKode(Roti **head, int kode) {
    if (*head == NULL) return;

    if ((*head)->kode == kode) {
        Roti *temp = *head;
        *head = (*head)->next;
        free(temp);
        return;
    }

    Roti *current = *head;
    while (current->next != NULL && current->next->kode != kode) {
        current = current->next;
    }

    if (current->next != NULL) {
        Roti *temp = current->next;
        current->next = temp->next;
        free(temp);
    }
}

// Fungsi untuk menghapus seluruh linked list
void deleteAllNodes(Roti **head) {
    Roti *current = *head;
    while (current != NULL) {
        Roti *temp = current;
        current = current->next;
        free(temp);
    }
    *head = NULL;
}

void createDatabase() {
    int jumlah;

    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== TOKO ROTI BAKERY ===================+\n");
    printf("+===================== Masukan Data Baru ==================+\n");
    printf("+==========================================================+\n\n");

    // Hapus database lama jika ada
    deleteAllNodes(&head);

    printf("        Berapa banyak roti yang mau diinputkan? \n\n");
    printf("+==========================================================+\n");
    printf("Jumlah: ");
    scanf("%d", &jumlah);
    getchar();

    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== Input Database =====================+\n");
    printf("+==========================================================+\n\n");

    for(int i = 0; i < jumlah; i++) {
        int kode, stok, harga;
        char nama[50];
        Tanggal tanggal;

        printf("\t\t\tRoti ke - %d\n", i + 1);
        printf("\t\t\tID roti\t\t: ");
        scanf("%d", &kode);
        getchar();

        printf("\t\t\tNama Roti\t: ");
        fgets(nama, 50, stdin);
        nama[strcspn(nama, "\n")] = '\0';

        printf("\t\t\tStok\t\t: ");
        scanf("%d", &stok);

        printf("\t\t\tHarga\t\t: ");
        scanf("%d", &harga);

        printf("\t\t\tTanggal Pembuatan (dd mm yyyy): ");
        scanf("%d %d %d", &tanggal.hari, &tanggal.bulan, &tanggal.tahun);
        getchar();

        Roti *newNode = createNode(kode, nama, stok, harga, tanggal);
        if (newNode != NULL) {
            insertAtEnd(&head, newNode);
        }
    }

    sprintf(pesan, "Admin menghapus data roti dengan ID: %d", jumlah);
    catatRiwayat(pesan);

    printf("+==========================================================+\n\n");
    printf("\t\t\tData berhasil dimasukkan. Tekan Enter untuk kembali ke menu...");
    getchar();
}

void readDatabase() {
    system(CLEAR_SCREEN);
    printf("+==========================================================================+\n");
    printf("+======================= TOKO ROTI BAKERY =================================+\n");
    printf("+=========================== READ DATABASE ================================+\n");
    printf("+==========================================================================+\n\n");

    if (head == NULL) {
        printf("\tBelum ada data roti!\n");
    } else {
        printf("ID\tNama Roti\t\tStok\tHarga\tTanggal Pembuatan\n");
        printf("=========================================================================\n");
        Roti *current = head;
        while (current != NULL) {
            printf("%d\t%-20s\t%d\t%d\t%02d-%02d-%04d\n",
                   current->kode,
                   current->nama,
                   current->stok,
                   current->harga,
                   current->tanggalPembuatan.hari,
                   current->tanggalPembuatan.bulan,
                   current->tanggalPembuatan.tahun);
            current = current->next;
        }
    }


sprintf(pesan, "Admin asd data roti dengan ID");
catatRiwayat(pesan);

    printf("\n+==========================================================================+\n");
    printf("\nTekan Enter untuk kembali ke menu...");
    getchar();
}

void addData() {
    int jumlah;
    system(CLEAR_SCREEN);

    printf("+==========================================================+\n");
    printf("+===================== TOKO ROTI BAKERY ===================+\n");
    printf("+===================== Tambah Data Roti ===================+\n");
    printf("+==========================================================+\n\n");

    printf("        Berapa banyak data roti yang ingin ditambahkan? \n\n");
    printf("+==========================================================+\n");
    printf("Jumlah: ");
    scanf("%d", &jumlah);
    getchar();

    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== Input Data Baru =====================+\n");
    printf("+==========================================================+\n\n");

    for (int i = 0; i < jumlah; i++) {
        int kode, stok, harga;
        char nama[50];
        Tanggal tanggal;

        printf("\nData Roti ke-%d\n", countData(head) + 1);

        printf("ID Roti       : ");
        scanf("%d", &kode);
        getchar();

        printf("Nama Roti     : ");
        fgets(nama, 50, stdin);
        nama[strcspn(nama, "\n")] = '\0';

        printf("Stok          : ");
        scanf("%d", &stok);

        printf("Harga         : ");
        scanf("%d", &harga);

        printf("Tanggal Pembuatan (dd mm yyyy): ");
        scanf("%d %d %d", &tanggal.hari, &tanggal.bulan, &tanggal.tahun);
        getchar();

        Roti *newNode = createNode(kode, nama, stok, harga, tanggal);
        if (newNode != NULL) {
            insertAtEnd(&head, newNode);
        }
    }

    catatRiwayat("Admin menambahkan %d data roti baru", jumlah);

    printf("\n+==========================================================+\n");
    printf("\t\tData berhasil ditambahkan. Tekan Enter untuk kembali...");
    getchar();
}

void deleteData() {
    int id;

    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== TOKO ROTI BAKERY ===================+\n");
    printf("+======================= Hapus Data ========================+\n");
    printf("+==========================================================+\n\n");

    if (head == NULL) {
        printf("\tBelum ada data roti untuk dihapus.\n");
        printf("\nTekan Enter untuk kembali...");
        getchar();
        return;
    }

    printf("Masukkan ID roti yang ingin dihapus: ");
    scanf("%d", &id);
    getchar();

    Roti *found = findNodeByKode(head, id);
    if (found == NULL) {
        printf("\n❌ Data roti dengan ID %d tidak ditemukan.\n", id);
    } else {
        deleteNodeByKode(&head, id);
        printf("\n✅ Data roti dengan ID %d berhasil dihapus.\n", id);
        catatRiwayat("Admin menghapus data roti dengan ID: %d", id);
    }

    printf("+==========================================================+\n\n");
    printf("\nTekan Enter untuk kembali ke menu...");
    getchar();
}

void editData() {
    int id;

    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== TOKO ROTI BAKERY ===================+\n");
    printf("+======================= Edit Data =========================+\n");
    printf("+==========================================================+\n\n");

    if (head == NULL) {
        printf("\tBelum ada data roti untuk diedit.\n");
        printf("\nTekan Enter untuk kembali...");
        getchar();
        return;
    }

    printf("Masukkan ID roti yang ingin diedit: ");
    scanf("%d", &id);
    getchar();

    Roti *found = findNodeByKode(head, id);
    if (found == NULL) {
        printf("\n❌ Data roti dengan ID %d tidak ditemukan.\n", id);
    } else {
        printf("\nData roti saat ini:\n");
        printf("ID Roti           : %d\n", found->kode);
        printf("Nama Roti         : %s\n", found->nama);
        printf("Stok              : %d\n", found->stok);
        printf("Harga             : %d\n", found->harga);
        printf("Tanggal Pembuatan : %02d-%02d-%d\n",
               found->tanggalPembuatan.hari,
               found->tanggalPembuatan.bulan,
               found->tanggalPembuatan.tahun);

        printf("\nMasukkan data baru:\n");

        printf("ID Roti Baru      : ");
        scanf("%d", &found->kode);
        getchar();

        printf("Nama Roti Baru    : ");
        fgets(found->nama, 50, stdin);
        found->nama[strcspn(found->nama, "\n")] = '\0';

        printf("Stok Baru         : ");
        scanf("%d", &found->stok);

        printf("Harga Baru        : ");
        scanf("%d", &found->harga);

        printf("Tanggal Pembuatan (dd mm yyyy): ");
        scanf("%d %d %d", &found->tanggalPembuatan.hari,
                         &found->tanggalPembuatan.bulan,
                         &found->tanggalPembuatan.tahun);

        printf("\n✅ Data roti berhasil diubah.\n");
        catatRiwayat("Admin mengedit data roti dengan ID %d", id);
    }

    printf("+==========================================================+\n\n");
    printf("\nTekan Enter untuk kembali ke menu...");
    getchar();
}

void searchData() {
    int pilihanCari, val, found = 0;
    char namaCari[50];
    Roti *foundNode = NULL;

    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== TOKO ROTI BAKERY ===================+\n");
    printf("+====================== Cari Data ==========================+\n");
    printf("+==========================================================+\n\n");

    if (head == NULL) {
        printf("Belum ada data roti untuk dicari.\n");
        printf("\nTekan Enter untuk kembali...");
        getchar();
        return;
    }

    printf("Cari berdasarkan:\n");
    printf("1. ID Roti\n");
    printf("2. Nama Roti\n");
    printf("3. Stok\n");
    printf("4. Harga\n");
    printf("5. Tanggal Pembuatan\n");
    printf("Masukkan pilihan (1-5): ");
    scanf("%d", &pilihanCari);
    getchar();

    switch (pilihanCari) {
        case 1:
            printf("Masukkan ID Roti: ");
            scanf("%d", &val);
            getchar();
            foundNode = findNodeByKode(head, val);
            if (foundNode != NULL) found = 1;
            catatRiwayat("Admin mencari data roti berdasarkan ID: %d", val);
            break;

        case 2:
            printf("Masukkan Nama Roti: ");
            fgets(namaCari, sizeof(namaCari), stdin);
            namaCari[strcspn(namaCari, "\n")] = '\0';
            {
                Roti *current = head;
                while (current != NULL) {
                    if (strcmp(current->nama, namaCari) == 0) {
                        foundNode = current;
                        found = 1;
                        break;
                    }
                    current = current->next;
                }
            }
            catatRiwayat("Admin mencari data roti berdasarkan Nama: %s", namaCari);
            break;

        case 3:
            printf("Masukkan jumlah stok: ");
            scanf("%d", &val);
            getchar();
            {
                Roti *current = head;
                while (current != NULL) {
                    if (current->stok == val) {
                        foundNode = current;
                        found = 1;
                        break;
                    }
                    current = current->next;
                }
            }
            catatRiwayat("Admin mencari data roti berdasarkan Stok: %d", val);
            break;

        case 4:
            printf("Masukkan harga: ");
            scanf("%d", &val);
            getchar();
            {
                Roti *current = head;
                while (current != NULL) {
                    if (current->harga == val) {
                        foundNode = current;
                        found = 1;
                        break;
                    }
                    current = current->next;
                }
            }
            catatRiwayat("Admin mencari data roti berdasarkan Harga: %d", val);
            break;

        case 5: {
            int h, b, t;
            printf("Masukkan tanggal pembuatan (format: dd mm yyyy): ");
            scanf("%d %d %d", &h, &b, &t);
            getchar();
            {
                Roti *current = head;
                while (current != NULL) {
                    if (current->tanggalPembuatan.hari == h &&
                        current->tanggalPembuatan.bulan == b &&
                        current->tanggalPembuatan.tahun == t) {
                        foundNode = current;
                        found = 1;
                        break;
                    }
                    current = current->next;
                }
            }
            catatRiwayat("Admin mencari data roti berdasarkan Tanggal: %02d-%02d-%04d", h, b, t);
            break;
        }

        default:
            printf("Pilihan tidak valid.\n");
            return;
    }

    if (found) {
        printf("\n✅ Data Roti Ditemukan:\n");
        printf("ID Roti           : %d\n", foundNode->kode);
        printf("Nama Roti         : %s\n", foundNode->nama);
        printf("Stok              : %d\n", foundNode->stok);
        printf("Harga             : %d\n", foundNode->harga);
        printf("Tanggal Pembuatan : %02d-%02d-%04d\n",
               foundNode->tanggalPembuatan.hari,
               foundNode->tanggalPembuatan.bulan,
               foundNode->tanggalPembuatan.tahun);
    } else {
        printf("\n❌ Data roti tidak ditemukan.\n");
    }

    printf("+==========================================================+\n\n");
    printf("Tekan Enter untuk kembali ke menu...");
    getchar();
}

void sortingData() {
    int pilihan, urutan;

    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== TOKO ROTI BAKERY ===================+\n");
    printf("+====================== Urutkan Data =======================+\n");
    printf("+==========================================================+\n\n");

    if (head == NULL) {
        printf("Belum ada data roti untuk diurutkan.\n");
        printf("\nTekan Enter untuk kembali...");
        getchar();
        return;
    }

    printf("Urut berdasarkan:\n");
    printf("1. ID Roti\n");
    printf("2. Nama Roti\n");
    printf("3. Stok\n");
    printf("4. Harga\n");
    printf("5. Tanggal Pembuatan\n");
    printf("Pilihan: ");
    scanf("%d", &pilihan);
    getchar();

    printf("\nUrutan:\n");
    printf("1. Ascending (A-Z atau Kecil ke Besar)\n");
    printf("2. Descending (Z-A atau Besar ke Kecil)\n");
    printf("Pilihan: ");
    scanf("%d", &urutan);
    getchar();

    // Bubble sort untuk linked list
    int swapped;
    Roti *ptr1;
    Roti *lptr = NULL;

    do {
        swapped = 0;
        ptr1 = head;

        while (ptr1->next != lptr) {
            int shouldSwap = 0;

            switch (pilihan) {
                case 1: // ID
                    if ((urutan == 1 && ptr1->kode > ptr1->next->kode) ||
                        (urutan == 2 && ptr1->kode < ptr1->next->kode))
                        shouldSwap = 1;
                    break;
                case 2: // Nama
                    if ((urutan == 1 && strcmp(ptr1->nama, ptr1->next->nama) > 0) ||
                        (urutan == 2 && strcmp(ptr1->nama, ptr1->next->nama) < 0))
                        shouldSwap = 1;
                    break;
                case 3: // Stok
                    if ((urutan == 1 && ptr1->stok > ptr1->next->stok) ||
                        (urutan == 2 && ptr1->stok < ptr1->next->stok))
                        shouldSwap = 1;
                    break;
                case 4: // Harga
                    if ((urutan == 1 && ptr1->harga > ptr1->next->harga) ||
                        (urutan == 2 && ptr1->harga < ptr1->next->harga))
                        shouldSwap = 1;
                    break;
                case 5: // Tanggal
                    {
                        int t1 = ptr1->tanggalPembuatan.tahun * 10000 + ptr1->tanggalPembuatan.bulan * 100 + ptr1->tanggalPembuatan.hari;
                        int t2 = ptr1->next->tanggalPembuatan.tahun * 10000 + ptr1->next->tanggalPembuatan.bulan * 100 + ptr1->next->tanggalPembuatan.hari;
                        if ((urutan == 1 && t1 > t2) || (urutan == 2 && t1 < t2))
                            shouldSwap = 1;
                    }
                    break;
            }

            if (shouldSwap) {
                // Swap data
                int tempKode = ptr1->kode;
                ptr1->kode = ptr1->next->kode;
                ptr1->next->kode = tempKode;

                char tempNama[50];
                strcpy(tempNama, ptr1->nama);
                strcpy(ptr1->nama, ptr1->next->nama);
                strcpy(ptr1->next->nama, tempNama);

                int tempStok = ptr1->stok;
                ptr1->stok = ptr1->next->stok;
                ptr1->next->stok = tempStok;

                int tempHarga = ptr1->harga;
                ptr1->harga = ptr1->next->harga;
                ptr1->next->harga = tempHarga;

                Tanggal tempTgl = ptr1->tanggalPembuatan;
                ptr1->tanggalPembuatan = ptr1->next->tanggalPembuatan;
                ptr1->next->tanggalPembuatan = tempTgl;

                swapped = 1;
            }
            ptr1 = ptr1->next;
        }
        lptr = ptr1;
    } while (swapped);

    // Catat riwayat
    const char *kolom[] = {"ID Roti", "Nama Roti", "Stok", "Harga", "Tanggal Pembuatan"};
    const char *order[] = {"Ascending", "Descending"};
    catatRiwayat("Admin sorting data roti berdasarkan %s (%s)", kolom[pilihan - 1], order[urutan - 1]);

    // Tampilkan hasil
    printf("\nHasil setelah diurutkan:\n");
    printf("+==================================================================================+\n");
    printf("| ID Roti | Nama Roti         | Stok | Harga | Tanggal Pembuatan                  |\n");
    printf("+==================================================================================+\n");
    Roti *current = head;
    while (current != NULL) {
        printf("| %-7d | %-18s | %-4d | %-5d | %02d-%02d-%04d                     |\n",
               current->kode, current->nama, current->stok, current->harga,
               current->tanggalPembuatan.hari, current->tanggalPembuatan.bulan, current->tanggalPembuatan.tahun);
        current = current->next;
    }
    printf("+==================================================================================+\n");

    printf("\nTekan Enter untuk kembali ke menu...");
    getchar();
}

void catatRiwayat(const char *format, ...) {
    time_t now;
    struct tm *t;
    char waktu[30];
    char isiLog[200];

    time(&now);
    t = localtime(&now);
    strftime(waktu, sizeof(waktu), "%d-%m-%Y %H:%M:%S", t);

    va_list args;
    va_start(args, format);
    vsnprintf(isiLog, sizeof(isiLog), format, args);
    va_end(args);

    snprintf(history[jumlahHistory], sizeof(history[jumlahHistory]), "[%s] %s", waktu, isiLog);
    jumlahHistory++;
}

void catatRiwayat2(const char *pesan) {
    time_t now;
    struct tm *t;
    char waktu[30];

    time(&now);
    t = localtime(&now);
    strftime(waktu, sizeof(waktu), "%d-%m-%Y %H:%M:%S", t);

    snprintf(history[jumlahHistory], sizeof(history[jumlahHistory]), "[%s] %s", waktu, pesan);
    jumlahHistory++;
}

void lihatRiwayat() {
    system(CLEAR_SCREEN);
    printf("+==========================================================+\n");
    printf("+===================== TOKO ROTI BAKERY ===================+\n");
    printf("+================== Riwayat Aktivitas User =================+\n");
    printf("+==========================================================+\n\n");

    if (jumlahHistory == 0) {
        printf("Belum ada aktivitas tercatat.\n");
    } else {
        for (int i = 0; i < jumlahHistory; i++) {
            printf("%d. %s\n", i + 1, history[i]);
        }
    }

    printf("\n+==========================================================+\n");
    printf("Tekan Enter untuk kembali ke menu...");
    getchar();
}

void kasir() {
    Pesanan pesanan[100];
    int jumlah_pesanan = 0;
    char opsi;

    system(CLEAR_SCREEN);
    printf("\t\t=================================\n");
    printf("\t\t||     KASIR TOKO ROTI BAKERY  ||\n");
    printf("\t\t=================================\n");

    if (head == NULL) {
        printf("\n❌ Belum ada data roti di database! Silakan input data terlebih dahulu.\n");
        printf("Tekan Enter untuk kembali ke menu...");
        getchar();
        return;
    }

    do {
        printf("\nMenu Roti:\n");
        Roti *current = head;
        while (current != NULL) {
            printf("%d. %s (Rp.%d)\n", current->kode, current->nama, current->harga);
            current = current->next;
        }

        int kodePesanan;
        printf("\nMasukkan kode roti: ");
        scanf("%d", &kodePesanan);

        int found = 0;
        Roti *current2 = head;
        while (current2 != NULL) {
            if (current2->kode == kodePesanan) {
                pesanan[jumlah_pesanan].kode = kodePesanan;
                pesanan[jumlah_pesanan].harga = current2->harga;
                strcpy(pesanan[jumlah_pesanan].nama, current2->nama);
                printf("Masukkan quantity: ");
                scanf("%d", &pesanan[jumlah_pesanan].qty);
                jumlah_pesanan++;
                found = 1;
                printf("✅ %s berhasil ditambahkan ke pesanan.\n", current2->nama);
                break;
            }
            current2 = current2->next;
        }

        if (!found) {
            printf("❌ Kode roti tidak ditemukan!\n");
        }

        printf("Tambah pesanan lagi? (y/n): ");
        scanf(" %c", &opsi);

    } while ((opsi == 'y' || opsi == 'Y') && jumlah_pesanan < 100);

    do {
        printf("\n===== Daftar Pesanan =====\n");
        for (int i = 0; i < jumlah_pesanan; i++) {
            printf("%d. %s (Rp.%d) x %d pcs = Rp.%d\n", i + 1, pesanan[i].nama, pesanan[i].harga, pesanan[i].qty, pesanan[i].harga * pesanan[i].qty);
        }
        printf("\n\tIngin Edit (e), Hapus (h), atau Lanjut Bayar (b)? ");
        scanf(" %c", &opsi);

        if(opsi == 'e') {
            int no;
            printf("Edit pesanan nomor: ");
            scanf("%d", &no);
            if (no < 1 || no > jumlah_pesanan) {
                printf("❌ Nomor tidak valid!\n");
                continue;
            }

            printf("Masukkan kode roti baru: ");
            int kodeBaru;
            scanf("%d", &kodeBaru);

            int ditemukan = 0;
            Roti *current3 = head;
            while (current3 != NULL) {
                if (current3->kode == kodeBaru) {
                    pesanan[no - 1].kode = current3->kode;
                    pesanan[no - 1].harga = current3->harga;
                    strcpy(pesanan[no - 1].nama, current3->nama);
                    printf("Masukkan quantity baru: ");
                    scanf("%d", &pesanan[no - 1].qty);
                    printf("✅ Pesanan berhasil diubah ke %s\n", current3->nama);
                    ditemukan = 1;
                    break;
                }
                current3 = current3->next;
            }

            if (!ditemukan) {
                printf("❌ Kode roti tidak valid!\n");
            }

        } else if (opsi == 'h') {
            int no;
            printf("Hapus pesanan nomor: ");
            scanf("%d", &no);
            if (no < 1 || no > jumlah_pesanan) {
                printf("❌ Nomor tidak valid!\n");
                continue;
            }

            for (int i = no - 1; i < jumlah_pesanan - 1; i++) {
                pesanan[i] = pesanan[i + 1];
            }
            jumlah_pesanan--;
            printf("✅ Pesanan berhasil dihapus.\n");
        }

    } while (opsi != 'b');

    int total = 0;
    printf("\nStruk Pembelian:\n");
    for (int i = 0; i < jumlah_pesanan; i++) {
        int subtotal = pesanan[i].harga * pesanan[i].qty;
        printf("%d. %s (Rp.%d) x %d = Rp.%d\n", i + 1, pesanan[i].nama, pesanan[i].harga, pesanan[i].qty, subtotal);
        total += subtotal;
    }

    printf("\nTOTAL BELANJA : Rp.%d\n", total);

    int bayar;
    printf("Masukkan jumlah uang bayar: Rp.");
    scanf("%d", &bayar);

    if (bayar >= total) {
        printf("Kembalian        : Rp.%d\n", bayar - total);
    } else {
        printf("❌ Uang kurang Rp.%d\n", total - bayar);
    }

    catatRiwayat("Transaksi kasir dilakukan sebanyak %d item", jumlah_pesanan);

    // Simpan nota ke file
    time_t t = time(NULL);
    struct tm waktu = *localtime(&t);

    char namaFile[100];
    sprintf(namaFile, "nota_%04d%02d%02d_%02d%02d%02d.txt",
            waktu.tm_year + 1900, waktu.tm_mon + 1, waktu.tm_mday,
            waktu.tm_hour, waktu.tm_min, waktu.tm_sec);

    FILE *file = fopen(namaFile, "w");
    if (file == NULL) {
        printf("❌ Gagal membuat file nota.\n");
    } else {
        fprintf(file, "================== NOTA PEMBELIAN ==================\n");
        fprintf(file, "Tanggal : %02d-%02d-%04d %02d:%02d:%02d\n\n",
                waktu.tm_mday, waktu.tm_mon + 1, waktu.tm_year + 1900,
                waktu.tm_hour, waktu.tm_min, waktu.tm_sec);

        for (int i = 0; i < jumlah_pesanan; i++) {
            int subtotal = pesanan[i].harga * pesanan[i].qty;
            fprintf(file, "%d. %s (Rp.%d) x %d = Rp.%d\n",
                    i + 1, pesanan[i].nama, pesanan[i].harga,
                    pesanan[i].qty, subtotal);
        }

        fprintf(file, "\nTOTAL BELANJA : Rp.%d\n", total);

        if (bayar >= total) {
            fprintf(file, "UANG BAYAR    : Rp.%d\n", bayar);
            fprintf(file, "KEMBALIAN     : Rp.%d\n", bayar - total);
        } else {
            fprintf(file, "UANG BAYAR    : Rp.%d\n", bayar);
            fprintf(file, "UANG KURANG   : Rp.%d\n", total - bayar);
        }

        fprintf(file, "====================================================\n");
        fprintf(file, "     TERIMA KASIH TELAH BELANJA DI TOKO BAKERY     \n");
        fprintf(file, "====================================================\n");

        fclose(file);
        printf("🧾 Nota pembelian telah disimpan ke file: %s\n", namaFile);
    }

    printf("\nTerima kasih telah berbelanja!\n");
    printf("Tekan Enter untuk kembali ke menu...");
    getchar(); getchar();
}

void showLoadingScreen() {
    system(CLEAR_SCREEN);
    printf("\n\n");
    printf("\t\t\tTOKO ROTI BAKERY\n");
    printf("\t\t\tLoading...\n\n");

    for(int i = 0; i < 50; i++) {
        printf("%c", 219);
        fflush(stdout);
        SLEEP(50);
    }
    printf("\n\n\t\t\tSystem Ready!\n");
    SLEEP(1000);
}

void showExitAnimation() {
    system(CLEAR_SCREEN);
    printf("\n\n");
    printf("\t\t\tTOKO ROTI BAKERY\n");
    printf("\t\t\tShutting Down...\n\n");

    // Animasi loading bar terbalik
    printf("\t\t\t");
    for(int i = 0; i < 50; i++) {
        printf("%c", 219);
        fflush(stdout);
        SLEEP(30);
    }
    printf("\n\n");

    // Animasi teks goodbye
    char goodbye[] = "Thank you for using our system!";
    printf("\t\t\t");
    for(int i = 0; i < strlen(goodbye); i++) {
        printf("%c", goodbye[i]);
        fflush(stdout);
        SLEEP(100);
    }
    printf("\n\n");

    // Animasi countdown
    printf("\t\t\t");
    for(int i = 3; i > 0; i--) {
        printf("Closing in %d...", i);
        fflush(stdout);
        SLEEP(1000);
        printf("\r\t\t\t                    \r"); // Clear line
    }

    // Final message
    printf("\t\t\tGoodbye! ��\n");
    SLEEP(500);
}

void getPassword(char *password, int maxLength) {
#ifdef _WIN32
    int i = 0;
    char ch;

    while ((ch = _getch()) != '\r' && i < maxLength - 1) {
        if (ch == '\b') {
            if (i > 0) {
                i--;
                printf("\b \b");
            }
        } else {
            password[i++] = ch;
            printf("*");
        }
    }
    password[i] = '\0';
    printf("\n");
#else
    struct termios oldt, newt;
    int i = 0;
    char ch;

    tcgetattr(STDIN_FILENO, &oldt);
    newt = oldt;
    newt.c_lflag &= ~(ECHO);
    tcsetattr(STDIN_FILENO, TCSANOW, &newt);

    while ((ch = getchar()) != '\n' && i < maxLength - 1) {
        if (ch == 127 || ch == '\b') {
            if (i > 0) {
                i--;
                printf("\b \b");
            }
        } else {
            password[i++] = ch;
            printf("*");
        }
    }
    password[i] = '\0';

    tcsetattr(STDIN_FILENO, TCSANOW, &oldt);
    printf("\n");
#endif
}

int main() {
    char username[50], password[50];
    const char correctUser[] = "a";
    const char correctPass[] = "a";
    time_t now;
    struct tm *currentTime;
    int pilihan;

    generate10Roti();

    do {
        showLoadingScreen();

        system(CLEAR_SCREEN);

        time(&now);
        currentTime = localtime(&now);

        printf("\n\n\t\t\tTOKO ROTI BAKERY\n\n");
        printf("\t==================================================\n");
        printf("\t><><><><><><><><><><><><><><><><><><><><><><><><><>\n");
        printf("\t==================================================\n\n");

        printf("\t\tUSERNAME :                               \n");
        printf("\t\tPASSWORD :                               \n");

        printf("\n\t==================================================\n");
        printf("\t\t\t  %s", asctime(currentTime));
        printf("\t==================================================\n");

        // Posisikan cursor untuk input username
        printf("\033[6A");     // naik 6 baris ke baris USERNAME
        printf("\033[27C");    // geser ke kanan setelah 'USERNAME : '
        fgets(username, sizeof(username), stdin);
        username[strcspn(username, "\n")] = '\0';

        // Posisikan cursor untuk input password
        printf("\033[27C");    // geser ke kanan setelah 'PASSWORD : '
        getPassword(password, sizeof(password));

        if (strcmp(username, correctUser) != 0 || strcmp(password, correctPass) != 0) {
            printf("\n\n\t❌ Username atau Password salah! Coba lagi...\n");
            SLEEP(2000);
        }

    } while (strcmp(username, correctUser) != 0 || strcmp(password, correctPass) != 0);

    do {
        system(CLEAR_SCREEN);

        printf("||==============================================================||\n");
        printf("||                TOKO ROTI BAKERY SYSTEM                      ||\n");
        printf("||==============================================================||\n");
        printf("||                                                              ||\n");
        printf("||    Menu:                                                     ||\n");
        printf("||    1. Membuat Database Baru Toko Roti                        ||\n");
        printf("||    2. Menampilkan Isi Database Toko Roti                     ||\n");
        printf("||    3. Menambahkan Data Roti Baru                             ||\n");
        printf("||    4. Menghapus Data Roti                                    ||\n");
        printf("||    5. Mengedit Data Roti                                     ||\n");
        printf("||    6. Mencari Data Roti                                      ||\n");
        printf("||    7. Melihat Riwayat Aktivitas                              ||\n");
        printf("||    8. Mengurutkan Data Roti                                  ||\n");
        printf("||    9. Kasir                                                  ||\n");
        printf("||    0. Keluar Program                                         ||\n");
        printf("||                                                              ||\n");
        printf("||==============================================================||\n");
        printf("||    Masukkan Pilihan :                                        ||\n");
        printf("||==============================================================||\n");

        printf("\033[2A");
        printf("\033[27C");
        scanf("%d", &pilihan);
        getchar();

        switch(pilihan) {
        case 1:
            createDatabase();
            break;
        case 2:
            readDatabase();
            break;
        case 3:
            addData();
            break;
        case 4:
            deleteData();
            break;
        case 5:
            editData();
            break;
        case 6:
            searchData();
            break;
        case 7:
            lihatRiwayat();
            break;
        case 8:
            sortingData();
            break;
        case 9:
            kasir();
            break;
        case 0:
            showExitAnimation();
            break;
        }
    } while(pilihan != 0);

    // Cleanup memory
    deleteAllNodes(&head);
    return 0;
}
