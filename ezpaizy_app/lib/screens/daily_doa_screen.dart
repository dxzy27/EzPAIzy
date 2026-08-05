import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class DailyDoaScreen extends StatefulWidget {
  const DailyDoaScreen({super.key});

  @override
  State<DailyDoaScreen> createState() => _DailyDoaScreenState();
}

class _DailyDoaScreenState extends State<DailyDoaScreen> {
  String _currentSituation = 'study';
  String _currentMode = 'normal'; // 'normal' or 'memorize'
  int _currentDoaIndex = 0;
  bool _isLoading = true;
  List<Map<String, dynamic>> _doas = [];

  // Memorize mode state
  int _memChunkIndex = 0;
  bool _isMemRevealed = false;

  final Map<String, List<Map<String, String>>> _fallbackDoas = {
    'study': [
      {
        'arabic': 'رَّبِّ زِدْنِي عِلْمًا',
        'english': 'O my Lord, increase me in knowledge.',
        'malay': 'Ya Tuhanku, tambahkanlah kepadaku ilmu pengetahuan.',
        'title': 'Doa Before Studying 1'
      },
      {
        'arabic': 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا',
        'english': 'O Allah, benefit me with what You have taught me, teach me what will benefit me, and increase me in knowledge.',
        'malay': 'Ya Allah, berilah manfaat kepadaku dengan apa yang Engkau ajarkan kepadaku, ajarkanlah aku apa yang bermanfaat bagiku, dan tambahkanlah ilmu kepadaku.',
        'title': 'Doa Before Studying 2'
      },
      {
        'arabic': 'اللَّهُمَّ لاَ سَهْلَ إِلاَّ مَا جَعَلْتَهُ سَهْلاً وَأَنْتَ تَجْعَلُ الْحَزْنَ إِذَا شِئْتَ سَهْلاً',
        'english': 'O Allah, nothing is easy except what You have made easy, and You can make difficulty easy if You wish.',
        'malay': 'Ya Allah, tiada yang mudah melainkan apa yang Engkau jadikan mudah, dan Engkau menjadikan kesusahan itu mudah jika Engkau menghendakinya.',
        'title': 'Doa Before Studying 3'
      },
      {
        'arabic': 'اللَّهُمَّ أَخْرِجْنَا مِنْ ظُلُمَاتِ الْوَهْمِ وَأَكْرِمْنَا بِنُورِ الْفَهْمِ',
        'english': 'O Allah, bring us out of the darkness of illusion and honor us with the light of understanding.',
        'malay': 'Ya Allah, keluarkanlah kami dari kegelapan waham dan muliakanlah kami dengan cahaya kefahaman.',
        'title': 'Doa Before Studying 4'
      },
      {
        'arabic': 'رَبِّ اشْرَحْ لِي صَدْرِي وَيَسِّرْ لِي أَمْرِي وَاحْلُلْ عُقْدَةً مِّن لِّسَانِي يَفْقَهُوا قَوْلِي',
        'english': 'O my Lord, expand for me my breast, and ease for me my task, and untie the knot from my tongue that they may understand my speech.',
        'malay': 'Ya Tuhanku, lapangkanlah dadaku, dan mudahkanlah urusanku, dan lepaskanlah ikatan dari lidahku, supaya mereka faham perkataanku.',
        'title': 'Doa Before Studying 5'
      },
    ],
    'exam': [
      {
        'arabic': 'رَبِّ اشْرَحْ لِي صَدْرِي وَيَسِّرْ لِي أَمْرِي',
        'english': 'O my Lord, expand for me my breast and ease for me my task.',
        'malay': 'Ya Tuhanku, lapangkanlah dadaku dan mudahkanlah urusanku.',
        'title': 'Doa For Exams 1'
      },
      {
        'arabic': 'يَا حَيُّ يَا قَيُّومُ بِرَحْمَتِكَ أَسْتَغِيثُ',
        'english': 'O Ever-Living, O Sustainer, by Your mercy I seek assistance.',
        'malay': 'Wahai Yang Maha Hidup, wahai Yang Maha Berdiri Sendiri, dengan rahmat-Mu aku memohon pertolongan.',
        'title': 'Doa For Exams 2'
      },
      {
        'arabic': 'اللَّهُمَّ اْفْتَحْ عَلَيْنَا فُتُوحَ الْعَارِفِينَ',
        'english': 'O Allah, open for us the openings of the knowledgeable.',
        'malay': 'Ya Allah, bukakanlah ke atas kami pembukaan orang-orang yang arif.',
        'title': 'Doa For Exams 3'
      },
      {
        'arabic': 'حَسْبُنَا اللَّهُ وَنِعْمَ الْوَكِيلُ',
        'english': 'Sufficient for us is Allah, and [He is] the best Disposer of affairs.',
        'malay': 'Cukuplah Allah bagi kami dan Dia adalah sebaik-baik Pelindung.',
        'title': 'Doa For Exams 4'
      },
      {
        'arabic': 'اللَّهُمَّ أَلْهِمْنِي رُشْدِي وَأَعِذْنِي مِنْ شَرِّ نَفْسِي',
        'english': 'O Allah, inspire me with guidance and protect me from the evil of myself.',
        'malay': 'Ya Allah, ilhamkanlah kepadaku petunjukku dan lindungilah aku dari kejahatan diriku.',
        'title': 'Doa For Exams 5'
      },
    ],
    'memory': [
      {
        'arabic': 'اللَّهُمَّ اهْدِنِي وَسَدِّدْنِي',
        'english': 'O Allah, guide me and keep me upright.',
        'malay': 'Ya Allah, berilah aku petunjuk dan tetapkanlah daku.',
        'title': 'Doa For Retention 1'
      },
      {
        'arabic': 'اللَّهُمَّ اجْعَلْ نَفْسِي مُطْمَئِنَّةً، تُؤْمِنُ بِلِقَائِكَ',
        'english': 'O Allah, make my soul tranquil, believing in Your meeting.',
        'malay': 'Ya Allah, jadikanlah jiwaku tenang, beriman dengan pertemuan dengan-Mu.',
        'title': 'Doa For Retention 2'
      },
      {
        'arabic': 'فَفَهَّمْنَاهَا سُلَيْمَانَ ۚ وَكُلًّا آتَيْنَا حُكْمًا وَعِلْمًا',
        'english': 'And We gave understanding of it to Solomon, and to each We gave judgment and knowledge.',
        'malay': 'Maka Kami berikan kefahaman tentang masalah itu kepada Sulaiman, dan kepada masing-masing Kami berikan hikmah dan ilmu.',
        'title': 'Doa For Retention 3'
      },
      {
        'arabic': 'سَنُقْرِئُكَ فَلَا تَنسَىٰ',
        'english': 'We will make you recite, [O Muhammad], and you will not forget.',
        'malay': 'Kami akan membacakan (Al-Quran) kepadamu (wahai Muhammad) maka engkau tidak akan lupa.',
        'title': 'Doa For Retention 4'
      },
      {
        'arabic': 'اللَّهُمَّ ثَبِّتْ حِفْظِي وَفَهْمِي',
        'english': 'O Allah, make firm my memorization and my understanding.',
        'malay': 'Ya Allah, tetapkanlah hafalanku dan kefahamanku.',
        'title': 'Doa For Retention 5'
      },
    ],
    'anxious': [
      {
        'arabic': 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْهَمِّ وَالْحَزَنِ',
        'english': 'O Allah, I seek refuge in You from anxiety and sorrow.',
        'malay': 'Ya Allah, aku berlindung kepada-Mu dari kegelisahan dan kesedihan.',
        'title': 'Doa When Feeling Anxious 1'
      },
      {
        'arabic': 'حَسْبِيَ اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ ۖ عَلَيْهِ تَوَكَّلْتُ',
        'english': 'Sufficient for me is Allah; there is no deity except Him. On Him I have relied.',
        'malay': 'Cukuplah Allah bagiku; tiada Tuhan melainkan Dia. Kepada-Nya aku bertawakal.',
        'title': 'Doa When Feeling Anxious 2'
      },
      {
        'arabic': 'لَّا إِلَٰهَ إِلَّا أَنتَ سُبْحَانَكَ إِنِّي كُنتُ مِنَ الظَّالِمِينَ',
        'english': 'There is no deity except You; exalted are You. Indeed, I have been of the wrongdoers.',
        'malay': 'Tiada Tuhan melainkan Engkau, Maha Suci Engkau. Sesungguhnya aku adalah dari golongan yang menzalimi diri sendiri.',
        'title': 'Doa When Feeling Anxious 3'
      },
      {
        'arabic': 'اللَّهُمَّ رَحْمَتَكَ أَرْجُو فَلَا تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ',
        'english': 'O Allah, it is Your mercy that I hope for, so do not leave me in charge of my affairs even for a blink of an eye.',
        'malay': 'Ya Allah, rahmat-Mu yang aku harapkan, maka janganlah Engkau serahkan urusanku kepada diriku sendiri walau sekelip mata.',
        'title': 'Doa When Feeling Anxious 4'
      },
      {
        'arabic': 'وَأُفَوِّضُ أَمْرِي إِلَى اللَّهِ ۚ إِنَّ اللَّهَ بَصِيرٌ بِالْعِبَادِ',
        'english': 'And I entrust my affair to Allah. Indeed, Allah is Seeing of [His] servants.',
        'malay': 'Dan aku menyerahkan urusanku kepada Allah. Sesungguhnya Allah Maha Melihat akan hamba-hamba-Nya.',
        'title': 'Doa When Feeling Anxious 5'
      },
    ],
    'unmotivated': [
      {
        'arabic': 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْعَجْزِ وَالْكَسَلِ',
        'english': 'O Allah, I seek refuge in You from weakness and laziness.',
        'malay': 'Ya Allah, aku berlindung kepada-Mu dari kelemahan dan kemalasan.',
        'title': 'Doa For Motivation 1'
      },
      {
        'arabic': 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْبُخْلِ وَالْجُبْنِ',
        'english': 'O Allah, I seek refuge in You from miserliness and cowardice.',
        'malay': 'Ya Allah, aku berlindung kepada-Mu dari kebakhilan dan sifat penakut.',
        'title': 'Doa For Motivation 2'
      },
      {
        'arabic': 'يَا مُقَلِّبَ الْقُلُوبِ ثَبِّتْ قَلْبِي عَلَى دِينِكَ',
        'english': 'O Turner of hearts, keep my heart steadfast on Your religion.',
        'malay': 'Wahai Tuhan yang membolak-balikkan hati, tetapkanlah hatiku di atas agama-Mu.',
        'title': 'Doa For Motivation 3'
      },
      {
        'arabic': 'اللَّهُمَّ أَعِنِّي عَلَى ذِكْرِكَ وَشُكْرِكَ وَحُسْنِ عِبَادَتِكَ',
        'english': 'O Allah, help me to remember You, to give You thanks, and to perform Your worship in the best manner.',
        'malay': 'Ya Allah, bantulah aku untuk mengingat-Mu, bersyukur kepada-Mu, dan beribadah kepada-Mu dengan sebaik-baiknya.',
        'title': 'Doa For Motivation 4'
      },
      {
        'arabic': 'رَبَّنَا آتِنَا مِن لَّدُنكَ رَحْمَةً وَهَيِّئْ لَنَا مِنْ أَمْرِنَا رَشَدًا',
        'english': 'Our Lord, grant us from Yourself mercy and prepare for us from our affair right guidance.',
        'malay': 'Wahai Tuhan kami, kurniakanlah rahmat dari sisi-Mu dan sediakanlah petunjuk dalam urusan kami.',
        'title': 'Doa For Motivation 5'
      },
    ],
  };

  @override
  void initState() {
    super.initState();
    _fetchDoas(_currentSituation);
  }

  Future<void> _fetchDoas(String situation) async {
    setState(() {
      _isLoading = true;
      _currentSituation = situation;
      _currentDoaIndex = 0;
      _memChunkIndex = 0;
      _isMemRevealed = false;
    });

    try {
      final res = await ApiService.getDailyDoa(situation: situation);
      if (res.containsKey('doas') && res['doas'] is List && (res['doas'] as List).isNotEmpty) {
        setState(() {
          _doas = List<Map<String, dynamic>>.from(res['doas']);
          _isLoading = false;
        });
      } else {
        _useFallback(situation);
      }
    } catch (_) {
      _useFallback(situation);
    }
  }

  void _useFallback(String situation) {
    final list = _fallbackDoas[situation] ?? _fallbackDoas['study']!;
    setState(() {
      _doas = list.map((item) => Map<String, dynamic>.from(item)).toList();
      _isLoading = false;
    });
  }

  List<String> get _currentDoaWords {
    if (_doas.isEmpty) return [];
    final text = _doas[_currentDoaIndex]['arabic'] ?? '';
    return text.split(' ').where((w) => w.trim().isNotEmpty).toList();
  }

  int get _totalMemChunks {
    final words = _currentDoaWords;
    if (words.isEmpty) return 1;
    return (words.length / 2).ceil();
  }

  String get _currentMemChunkText {
    final words = _currentDoaWords;
    if (words.isEmpty) return '';
    final start = _memChunkIndex * 2;
    final end = (start + 2 < words.length) ? start + 2 : words.length;
    if (start >= words.length) return words.last;
    return words.sublist(start, end).join(' ');
  }

  Future<void> _playRecitation() async {
    final audioUrl = _doas.isNotEmpty ? _doas[_currentDoaIndex]['audio'] : null;
    if (audioUrl != null && audioUrl.toString().isNotEmpty) {
      final Uri uri = Uri.parse(audioUrl);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
        return;
      }
    }
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Playing recitation audio...')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final dateStr = DateFormat('EEEE, MMM dd, yyyy').format(DateTime.now());

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Header Bar
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      InkWell(
                        onTap: () {
                          if (context.canPop()) {
                            context.pop();
                          } else {
                            context.go('/dashboard');
                          }
                        },
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          width: 38,
                          height: 38,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.grey.shade400),
                            color: Colors.white,
                          ),
                          child: const Icon(Icons.arrow_back, size: 18, color: Colors.black87),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Daily Doa',
                            style: GoogleFonts.outfit(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF0F172A),
                            ),
                          ),
                          Text(
                            'Your daily dose of supplications and guidance.',
                            style: GoogleFonts.outfit(
                              fontSize: 11,
                              color: Colors.grey.shade600,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.calendar_today_outlined, size: 12, color: Colors.grey),
                        const SizedBox(width: 4),
                        Text(
                          dateStr,
                          style: GoogleFonts.outfit(fontSize: 10, color: Colors.grey.shade700),
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 20),

              // Main Card Container
              Card(
                elevation: 4,
                shadowColor: Colors.black12,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                clipBehavior: Clip.antiAlias,
                child: Column(
                  children: [
                    // Dark Navy Header Banner
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: Column(
                        children: [
                          const Icon(
                            Icons.nights_stay,
                            color: Color(0xFFFFC107),
                            size: 36,
                          ),
                          const SizedBox(height: 6),
                          Text(
                            _doas.isNotEmpty ? (_doas[_currentDoaIndex]['title'] ?? '').toString().toUpperCase() : 'DAILY DOA',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.outfit(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                              letterSpacing: 2.0,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Situation & Mode Selector Container
                    Container(
                      color: const Color(0xFFF8FAFC),
                      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
                      child: Column(
                        children: [
                          // Row 1: Situation Selector
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                Text(
                                  'Situation: ',
                                  style: GoogleFonts.outfit(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.grey.shade600,
                                  ),
                                ),
                                _buildSituationPill('study', '📚 Studying'),
                                _buildSituationPill('exam', '📝 Exam'),
                                _buildSituationPill('memory', '🧠 Memory'),
                                _buildSituationPill('anxious', '😰 Anxious'),
                                _buildSituationPill('unmotivated', '😐 Unmotivated'),
                              ],
                            ),
                          ),
                          const Divider(height: 16, thickness: 1, color: Color(0xFFE2E8F0)),
                          // Row 2: Reading Mode Selector
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                'Reading Mode: ',
                                style: GoogleFonts.outfit(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.grey.shade600,
                                ),
                              ),
                              _buildModeButton('normal', 'Normal'),
                              const SizedBox(width: 8),
                              _buildModeButton('memorize', 'Memorize'),
                            ],
                          ),
                        ],
                      ),
                    ),

                    const Divider(height: 1, color: Color(0xFFE2E8F0)),

                    // Card Content Body
                    Container(
                      color: Colors.white,
                      padding: const EdgeInsets.all(20),
                      child: _isLoading
                          ? const Center(
                              child: Padding(
                                padding: EdgeInsets.all(40.0),
                                child: CircularProgressIndicator(),
                              ),
                            )
                          : Column(
                              children: [
                                // Navigation Slider Controls
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    IconButton(
                                      onPressed: _currentDoaIndex > 0
                                          ? () {
                                              setState(() {
                                                _currentDoaIndex--;
                                                _memChunkIndex = 0;
                                                _isMemRevealed = false;
                                              });
                                            }
                                          : null,
                                      icon: const Icon(Icons.chevron_left),
                                      style: IconButton.styleFrom(
                                        side: BorderSide(
                                          color: _currentDoaIndex > 0
                                              ? const Color(0xFF475569)
                                              : Colors.grey.shade300,
                                          width: 1.5,
                                        ),
                                        foregroundColor: const Color(0xFF475569),
                                      ),
                                    ),
                                    Text(
                                      '${_currentDoaIndex + 1} / ${_doas.length}',
                                      style: GoogleFonts.outfit(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.grey.shade600,
                                      ),
                                    ),
                                    IconButton(
                                      onPressed: _currentDoaIndex < _doas.length - 1
                                          ? () {
                                              setState(() {
                                                _currentDoaIndex++;
                                                _memChunkIndex = 0;
                                                _isMemRevealed = false;
                                              });
                                            }
                                          : null,
                                      icon: const Icon(Icons.chevron_right),
                                      style: IconButton.styleFrom(
                                        side: BorderSide(
                                          color: _currentDoaIndex < _doas.length - 1
                                              ? const Color(0xFF475569)
                                              : Colors.grey.shade300,
                                          width: 1.5,
                                        ),
                                        foregroundColor: const Color(0xFF475569),
                                      ),
                                    ),
                                  ],
                                ),

                                const SizedBox(height: 16),

                                // Mode Display
                                if (_currentMode == 'normal') _buildNormalMode() else _buildMemorizeMode(),
                              ],
                            ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSituationPill(String key, String label) {
    final isSelected = _currentSituation == key;
    return Padding(
      padding: const EdgeInsets.only(right: 6.0),
      child: InkWell(
        onTap: () => _fetchDoas(key),
        borderRadius: BorderRadius.circular(50),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFF2563EB) : Colors.white,
            borderRadius: BorderRadius.circular(50),
            border: Border.all(
              color: isSelected ? const Color(0xFF2563EB) : const Color(0xFF3B82F6).withValues(alpha: 0.3),
              width: 1,
            ),
          ),
          child: Text(
            label,
            style: GoogleFonts.outfit(
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              color: isSelected ? Colors.white : const Color(0xFF2563EB),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildModeButton(String modeKey, String label) {
    final isSelected = _currentMode == modeKey;
    return InkWell(
      onTap: () {
        setState(() {
          _currentMode = modeKey;
          _memChunkIndex = 0;
          _isMemRevealed = false;
        });
      },
      borderRadius: BorderRadius.circular(50),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF212529) : Colors.white,
          borderRadius: BorderRadius.circular(50),
          border: Border.all(
            color: isSelected ? const Color(0xFF212529) : const Color(0xFF6C757D),
            width: 1,
          ),
        ),
        child: Text(
          label,
          style: GoogleFonts.outfit(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            color: isSelected ? Colors.white : const Color(0xFF212529),
          ),
        ),
      ),
    );
  }

  Widget _buildNormalMode() {
    final doa = _doas[_currentDoaIndex];

    return Column(
      children: [
        // Arabic Hero Text
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 16.0),
          child: Text(
            doa['arabic'] ?? '',
            textAlign: TextAlign.center,
            style: GoogleFonts.amiri(
              fontSize: 38,
              fontWeight: FontWeight.bold,
              height: 2.1,
              color: const Color(0xFF0F172A),
            ),
          ),
        ),

        const SizedBox(
          width: 100,
          child: Divider(thickness: 1, color: Color(0xFFE2E8F0)),
        ),

        const SizedBox(height: 12),

        // English Translation
        Text(
          'ENGLISH',
          style: GoogleFonts.outfit(
            fontSize: 9,
            fontWeight: FontWeight.bold,
            color: Colors.grey.shade500,
            letterSpacing: 1.5,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          '"${doa['english'] ?? ''}"',
          textAlign: TextAlign.center,
          style: GoogleFonts.outfit(
            fontSize: 14,
            fontStyle: FontStyle.italic,
            color: Colors.black87,
            height: 1.5,
          ),
        ),

        const SizedBox(height: 16),

        // Bahasa Melayu Translation
        Text(
          'BAHASA MELAYU',
          style: GoogleFonts.outfit(
            fontSize: 9,
            fontWeight: FontWeight.bold,
            color: Colors.grey.shade500,
            letterSpacing: 1.5,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          '"${doa['malay'] ?? ''}"',
          textAlign: TextAlign.center,
          style: GoogleFonts.outfit(
            fontSize: 14,
            fontStyle: FontStyle.italic,
            color: Colors.black87,
            height: 1.5,
          ),
        ),

        const SizedBox(height: 24),

        // Play Recitation Button
        InkWell(
          onTap: _playRecitation,
          borderRadius: BorderRadius.circular(50),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(50),
              border: Border.all(color: Colors.grey.shade200),
              boxShadow: const [
                BoxShadow(
                  color: Colors.black12,
                  blurRadius: 10,
                  offset: Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: const BoxDecoration(
                    color: Color(0xFF1BBC9B),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.play_arrow, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 12),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '▶ Play Recitation',
                      style: GoogleFonts.outfit(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF0F172A),
                      ),
                    ),
                    Text(
                      'Play Audio',
                      style: GoogleFonts.outfit(
                        fontSize: 10,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  Widget _buildMemorizeMode() {
    final doa = _doas[_currentDoaIndex];

    return Column(
      children: [
        // Memorize Card Area (Tap to Reveal)
        GestureDetector(
          onTap: () {
            setState(() {
              _isMemRevealed = !_isMemRevealed;
            });
          },
          child: Container(
            width: double.infinity,
            constraints: const BoxConstraints(minHeight: 200),
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: Colors.grey.shade300,
                style: BorderStyle.solid,
                width: 1.5,
              ),
            ),
            child: Stack(
              alignment: Alignment.center,
              children: [
                // Text Chunk
                Text(
                  _currentMemChunkText,
                  textAlign: TextAlign.center,
                  style: GoogleFonts.amiri(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    height: 2.0,
                    color: const Color(0xFF0F172A),
                  ),
                ),

                // Blur Overlay
                if (!_isMemRevealed)
                  Positioned.fill(
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.92),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.visibility_off_outlined, color: Colors.grey.shade500, size: 36),
                          const SizedBox(height: 8),
                          Text(
                            'Tap to Reveal',
                            style: GoogleFonts.outfit(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey.shade600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),

        const SizedBox(height: 16),

        // Chunk Navigation Row
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            IconButton(
              onPressed: _memChunkIndex > 0
                  ? () {
                      setState(() {
                        _memChunkIndex--;
                        _isMemRevealed = false;
                      });
                    }
                  : null,
              icon: const Icon(Icons.arrow_back, size: 18),
              style: IconButton.styleFrom(
                backgroundColor: _memChunkIndex > 0 ? const Color(0xFF3B82F6) : Colors.grey.shade300,
                foregroundColor: Colors.white,
              ),
            ),
            const SizedBox(width: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '${_memChunkIndex + 1} / $_totalMemChunks',
                style: GoogleFonts.outfit(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey.shade700,
                ),
              ),
            ),
            const SizedBox(width: 12),
            IconButton(
              onPressed: _memChunkIndex < _totalMemChunks - 1
                  ? () {
                      setState(() {
                        _memChunkIndex++;
                        _isMemRevealed = false;
                      });
                    }
                  : null,
              icon: const Icon(Icons.arrow_forward, size: 18),
              style: IconButton.styleFrom(
                backgroundColor: _memChunkIndex < _totalMemChunks - 1 ? const Color(0xFF3B82F6) : Colors.grey.shade300,
                foregroundColor: Colors.white,
              ),
            ),
          ],
        ),

        const SizedBox(height: 16),

        // Full Translation Reference below
        Text(
          '"${doa['english'] ?? ''}"',
          textAlign: TextAlign.center,
          style: GoogleFonts.outfit(
            fontSize: 13,
            fontStyle: FontStyle.italic,
            color: Colors.grey.shade700,
          ),
        ),
      ],
    );
  }
}
