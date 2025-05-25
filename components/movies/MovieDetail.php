<?php
require_once __DIR__ . '/../../config/db_connect.php';

function renderMovieDetail($movieId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM movies WHERE movie_id = ?");
    $stmt->execute([$movieId]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$movie) {
        echo '<div class="alert alert-danger">Film bulunamadı.</div>';
        return;
    }

    // Seansları çek
    $sessionStmt = $db->prepare("SELECT * FROM sessions WHERE movie_id = ? AND status = 'available' ORDER BY price ASC");
    $sessionStmt->execute([$movieId]);
    $sessions = $sessionStmt->fetchAll(PDO::FETCH_ASSOC);
    $minPrice = null;
    if ($sessions && count($sessions) > 0) {
        $minPrice = $sessions[0]['price'];
    }

    $secilen_session_id = $_GET['session_id'] ?? ($sessions[0]['session_id'] ?? null);
    $kapasite = 200; // Varsayılan
    if ($secilen_session_id) {
        $stmt = $db->prepare("SELECT t.capacity FROM theaters t JOIN sessions s ON t.theater_id = s.theater_id WHERE s.session_id = ?");
        $stmt->execute([$secilen_session_id]);
        $kapasite = $stmt->fetchColumn() ?: 200; // Kapasite yoksa varsayılan 200
    }
    ?>
    <style>
        .ticket-area{
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .screen {
        width: 300px;
        height: 50px;
        background-color: #333;
        color: white;
        text-align: center;
        line-height: 50px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    .seats-container {
      display: grid;
      grid-template-columns: repeat(10, 40px);
      gap: 5px;
      margin-bottom: 20px;
    }
    .seat {
      width: 40px;
      height: 40px;
      background-color: #44c767;
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 12px;
    }
    .seat.selected {
      background-color: #ff4444;
    }
    .seat.occupied {
      background-color: #999;
      cursor: not-allowed;
    }
    .info {
      text-align: center;
    }
    .info p {
      margin: 5px 0;
      font-size: 16px;
    }


    .card-wrapper {
        perspective: 1000px;
    }
    .credit-card {
    width: 340px;
    height: 200px;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.8s ease;
    margin-bottom: 1rem;
    }
    .credit-card .front, .credit-card .back {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    backface-visibility: hidden;
    background: linear-gradient(135deg, #3e8e41, #1b5e20);
    color: white;
    }
    .credit-card .back {
    background: linear-gradient(135deg, #555, #333);
    transform: rotateY(180deg);
    }
    .credit-card.flipped {
    transform: rotateY(180deg);
    }
    .card-number {
    font-size: 1.2rem;
    margin-bottom: 20px;
    }
    .card-name, .card-expiry, .cvv-box {
    font-size: 1rem;
    }
    </style>
    <div class="movie-detail-container">
        <div class="movie-header">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <div class="movie-poster-detail">
                            <img src="../../<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                 class="img-fluid rounded">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h1 class="movie-title-detail"><?php echo htmlspecialchars($movie['title']); ?></h1>
                        <div class="movie-meta-detail">
                            <span class="badge bg-primary me-2"><?php echo htmlspecialchars($movie['category_id']); ?></span>
                            <span class="me-3">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo htmlspecialchars($movie['duration']); ?>
                            </span>
                        </div>
                        <div class="movie-info-detail mt-4">
                            <p class="lead"><?php echo htmlspecialchars($movie['description']); ?></p>
                            <div class="movie-credits mt-4">
                                <?php if (!empty($movie['director'])): ?>
                                    <p><strong>Yönetmen:</strong> <?php echo htmlspecialchars($movie['director']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($movie['cast'])): ?>
                                    <p><strong>Oyuncular:</strong> <?php echo htmlspecialchars($movie['cast']); ?></p>
                                <?php endif; ?>
                                <p><strong>Vizyon Tarihi:</strong> <?php echo date('d.m.Y', strtotime($movie['release_date'])); ?></p>
                                <?php if ($minPrice !== null): ?>
                                    <p><strong>Başlangıç Ücreti:</strong> <?php echo number_format($minPrice, 2); ?> ₺</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rezervasyon Bölümü -->
        <div class="movie-reservation mt-5">
            <div class="container">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <h2 class="mb-4">Bilet Rezervasyonu</h2>
                    <?php if ($sessions && count($sessions) > 0): ?>
                        <form action="../../components/movies/book_ticket.php" method="POST">
                            <input type="hidden" name="movie_id" value="<?php echo $movieId; ?>">
                            <input type="hidden" name="session_id" id="session_id_input" value="<?php echo $session['session_id']; ?>">
                            <input type="hidden" name="selected_seats" id="selected_seats_input">
                            <input type="hidden" name="total_amount" id="total_amount_input">
                            <div class="mb-3">
                                <label for="session_id" class="form-label">Salon & Seans Seçin</label>
                                <select class="form-select" id="session_id" name="session_id" required>
                                    <option value="">Salon & Seans Seçiniz</option>
                                    <?php foreach ($sessions as $session): ?>
                                        <option 
                                            value="<?php echo $session['session_id']; ?>" 
                                            data-theater-id="<?php echo $session['theater_id']; ?>" 
                                            data-price="<?php echo $session['price']; ?>">
                                                Salon: <?php echo htmlspecialchars($session['theater_id']); ?> - 
                                                Tarih: <?php echo date('d.m.Y', strtotime($session['session_date'])); ?> - 
                                                Saat: <?php echo htmlspecialchars($session['session_time']); ?> - 
                                                Ücret: <?php echo number_format($session['price'], 2); ?> ₺ 
                                        </option>


                                    <?php endforeach; ?>
                                </select>
                            </div>
                            

                            <div class="ticket-area">
                                <div class="screen">Perde</div>
                                <div class="seats-container" id="seats" data-kapasite="<?php echo htmlspecialchars($kapasite); ?>"></div>
                                <div class="info">
                                    <p>Seçilen Koltuklar: <span id="selected-seats"></span></p>
                                    <p>Toplam Koltuk: <span id="total-seats">0</span></p>
                                </div>
                            </div>

                            <!-- Ödeme Kartı Alanı -->
                            <div id="payment-section" class="mt-4" style="display: none;">
                            <h5 class="mb-3">Ödeme Bilgileri</h5>

                            <!-- Sanal Kart Görseli -->
                            <div class="card-wrapper mb-3">
                                <div class="credit-card" id="credit-card">
                                <div class="front">
                                    <div class="card-number" id="card-number-display">#### #### #### ####</div>
                                    <div class="card-name" id="card-name-display">İsim Soyisim</div>
                                    <div class="card-expiry" id="card-expiry-display">MM/YY</div>
                                </div>
                                <div class="back">
                                    <div class="cvv-box" id="cvv-display">CVV</div>
                                </div>
                                </div>
                            </div>

                            <!-- Kart Giriş Alanları -->
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Kart Üzerindeki İsim</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="card_number" class="form-label">Kart Numarası</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" maxlength="19" placeholder="XXXX XXXX XXXX XXXX" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Son Kullanma Tarihi</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" minlength="5" maxlength="5" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" maxlength="3" required>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                Toplam Ödenecek Tutar: <strong><span id="total-amount">0.00</span> ₺</strong>
                            </div>
                            </div>



                            <button type="submit" class="btn btn-primary">Rezervasyon Yap</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">Bu film için uygun seans bulunamadı.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info mt-4">
                        Rezervasyon yapabilmek için <a href="../../pages/login">giriş yapın</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
?>
<script>

document.addEventListener('DOMContentLoaded', () => {
    const sessionSelect = document.getElementById('session_id');
    const seatsContainer = document.getElementById('seats');
    const selectedSeatsDisplay = document.getElementById('selected-seats');
    const selectedSeatsInput = document.getElementById('selected_seats_input');
    const totalSeatsDisplay = document.getElementById('total-seats');
    const cols = 10;
    
    const card = document.getElementById('credit-card');

    const cardNameInput = document.getElementById('card_name');
    const cardNumberInput = document.getElementById('card_number');
    const expiryInput = document.getElementById('expiry_date');
    const cvvInput = document.getElementById('cvv');

    const displayName = document.getElementById('card-name-display');
    const displayNumber = document.getElementById('card-number-display');
    const displayExpiry = document.getElementById('card-expiry-display');
    const displayCVV = document.getElementById('cvv-display');

    const totalAmountDisplay = document.getElementById('total-amount');
    const totalAmountInput = document.getElementById('total_amount_input');
      
      let selectedSeats = [];

      // Seans değiştiğinde koltukları güncelle
      sessionSelect.addEventListener('change', () => {
        const selectedOption = sessionSelect.options[sessionSelect.selectedIndex];
        const theaterId = selectedOption.dataset.theaterId;
        

        // Kapasiteyi almak için AJAX isteği
        fetch("/web/cinema-ticket/components/movies/get_kapasite.php?theater_id=" + theaterId)
          .then(response => response.json())
          .then(data => {
            const kapasite = data.kapasite || 200; // Varsayılan 200
            seatsContainer.dataset.kapasite = kapasite;
            console.log("seans kapasitesi: "+kapasite);
            createSeats(kapasite);
          })
          .catch(error => {
            console.error('Kapasite alınamadı:', error);
            seatsContainer.dataset.kapasite = 200;
            createSeats(200);
          });
      });

      // Koltukları oluştur
      async function createSeats(kapasite) {
        const rows = Math.ceil(kapasite / cols);
        seatsContainer.innerHTML = '';
        
        const selectSessionInput = document.getElementById('session_id');
        const sessionId = selectSessionInput.value; 


        // Dolu koltukları çek
        let occupiedSeats = [];
        try {
            const response = await fetch(`../../components/movies/get_occupied_seats.php?session_id=${sessionId}`);
            const data = await response.json();
            if (data.occupied_seats) {
                occupiedSeats = data.occupied_seats;
            }
        } catch (error) {
            console.error('Dolu koltuklar alınamadı:', error);
        }

        for (let i = 0; i < rows; i++) {
            for (let j = 0; j < cols; j++) {
                const seatIndex = i * cols + j;
                if (seatIndex >= kapasite) break;

                const seatNumber = `${i + 1}-${j + 1}`;
                const seat = document.createElement('div');
                seat.classList.add('seat');
                seat.dataset.row = i + 1;
                seat.dataset.col = j + 1;
                seat.textContent = seatNumber;

                if (occupiedSeats.includes(seatNumber)) {
                    seat.classList.add('occupied');
                } else {
                    seat.addEventListener('click', () => selectSeat(seat));
                }

                seatsContainer.appendChild(seat);
            }
        }

        // Seçilen koltukları sıfırla ve güncelle
        selectedSeats = [];
        updateSelectedSeats();
}


      // Koltuk seçme işlemi
      function selectSeat(seat) {
        if (seat.classList.contains('occupied')) return;
        seat.classList.toggle('selected');
        const seatId = `${seat.dataset.row}-${seat.dataset.col}`;
        if (seat.classList.contains('selected')) {
          selectedSeats.push(seatId);
        } else {
          selectedSeats = selectedSeats.filter(id => id !== seatId);
        }
        updateSelectedSeats();
      }

      // Seçilen koltukları güncelle
      function updateSelectedSeats() {
  // Seçilen koltukları göster (örn. "8-10, 8-11")
        selectedSeatsDisplay.textContent = selectedSeats.join(', ') || 'Yok';
        totalSeatsDisplay.textContent = selectedSeats.length;

        // Gizli input'a seçilen koltukları JSON olarak yaz
        selectedSeatsInput.value = JSON.stringify(selectedSeats);

        const paymentCard = document.getElementById('payment-section');
        const selectedOption = sessionSelect.options[sessionSelect.selectedIndex];
        const seatPrice = parseFloat(selectedOption.dataset.price || 0);

        if (selectedSeats.length > 0) {
            if (paymentCard) paymentCard.style.display = 'block';
            const total = (selectedSeats.length * seatPrice).toFixed(2);
            totalAmountDisplay.textContent = total;
            totalAmountInput.value = total; // Toplam tutarı gizli input'a yaz
        } else {
            if (paymentCard) paymentCard.style.display = 'none';
            totalAmountDisplay.textContent = '0.00';
            totalAmountInput.value = '0.00'; // Koltuk yoksa input'u sıfırla
            selectedSeatsInput.value = ''; // Koltuk yoksa seçili koltuk input'unu boşalt
        }
}




      cardNameInput.addEventListener('input', () => {
        displayName.textContent = cardNameInput.value || 'İsim Soyisim';
        });

        cardNumberInput.addEventListener('input', () => {
        let value = cardNumberInput.value.replace(/\D/g, '').substring(0, 16);
        value = value.replace(/(.{4})/g, '$1 ').trim();
        cardNumberInput.value = value;
        displayNumber.textContent = value || '#### #### #### ####';
        });

        expiryInput.addEventListener('input', () => {
        let val = expiryInput.value.replace(/\D/g, '').substring(0, 4);
        if (val.length >= 3) val = val.substring(0, 2) + '/' + val.substring(2);
        expiryInput.value = val;
        displayExpiry.textContent = val || 'MM/YY';
        });

        cvvInput.addEventListener('focus', () => card.classList.add('flipped'));
        cvvInput.addEventListener('blur', () => card.classList.remove('flipped'));
        cvvInput.addEventListener('input', () => {
        displayCVV.textContent = cvvInput.value || 'CVV';
        });


      // İlk yüklemede koltukları oluştur
      const initialKapasite = parseInt(seatsContainer.dataset.kapasite) || 200;
      createSeats(initialKapasite);
    });
</script> 