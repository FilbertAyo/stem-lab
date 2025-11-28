<?php

namespace Database\Seeders;

use App\Models\Model3d;
use App\Models\ModelQuestion;
use Illuminate\Database\Seeder;

class ModelQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Questions organized by model name
        $questionsData = [
            // ==================== PHYSICS ====================
            'Spring Balance' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What physical principle does a spring balance work on?',
                    'options' => ['Hooke\'s Law', 'Newton\'s Third Law', 'Archimedes\' Principle', 'Pascal\'s Law'],
                    'correct_answer' => 'Hooke\'s Law',
                    'explanation' => 'A spring balance works on Hooke\'s Law which states that the extension of a spring is directly proportional to the applied force.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the SI unit measured by a spring balance?',
                    'options' => ['Newton (N)', 'Kilogram (kg)', 'Meter (m)', 'Joule (J)'],
                    'correct_answer' => 'Newton (N)',
                    'explanation' => 'A spring balance measures force or weight in Newtons (N), the SI unit of force.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'A spring balance can only measure weight, not mass.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Spring balances measure the gravitational force (weight) acting on an object, not its mass directly.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What happens to the reading of a spring balance if taken to the moon?',
                    'options' => ['It decreases', 'It increases', 'It remains the same', 'It becomes zero'],
                    'correct_answer' => 'It decreases',
                    'explanation' => 'The reading decreases because the moon has less gravitational pull than Earth (about 1/6th).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Another name for a spring balance is:',
                    'options' => ['Newton meter', 'Beam balance', 'Triple beam balance', 'Electronic scale'],
                    'correct_answer' => 'Newton meter',
                    'explanation' => 'A spring balance is also called a Newton meter because it measures force in Newtons.',
                ],
            ],

            'Eureka Can (Overflow Can)' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What principle is demonstrated using a Eureka can?',
                    'options' => ['Archimedes\' Principle', 'Pascal\'s Law', 'Bernoulli\'s Principle', 'Boyle\'s Law'],
                    'correct_answer' => 'Archimedes\' Principle',
                    'explanation' => 'The Eureka can is used to demonstrate Archimedes\' Principle - the upthrust on a body equals the weight of fluid displaced.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What does the Eureka can measure?',
                    'options' => ['Volume of displaced water', 'Weight of object', 'Density of liquid', 'Temperature of water'],
                    'correct_answer' => 'Volume of displaced water',
                    'explanation' => 'The Eureka can collects and measures the volume of water displaced when an object is immersed.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'The Eureka can was named after Archimedes\' famous exclamation "Eureka!"',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Yes, it\'s named after Archimedes\' legendary "Eureka!" moment when he discovered the principle of buoyancy.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Before using a Eureka can, it should be filled with water until:',
                    'options' => ['Water overflows from the spout', 'It is half full', 'It is completely empty', 'Water reaches the brim'],
                    'correct_answer' => 'Water overflows from the spout',
                    'explanation' => 'The can must be filled until water overflows and stops dripping from the spout to ensure accurate displacement measurement.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is collected in the measuring cylinder when using a Eureka can?',
                    'options' => ['Displaced water', 'The object', 'Air bubbles', 'Oil'],
                    'correct_answer' => 'Displaced water',
                    'explanation' => 'The displaced water flows through the spout and is collected in a measuring cylinder to measure its volume.',
                ],
            ],

            'Bar Magnet' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How many poles does a bar magnet have?',
                    'options' => ['Two', 'One', 'Three', 'Four'],
                    'correct_answer' => 'Two',
                    'explanation' => 'A bar magnet has two poles: a North pole and a South pole.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Like poles of magnets attract each other.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'Like poles (N-N or S-S) repel each other. Unlike poles (N-S) attract each other.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Where is the magnetic field strongest on a bar magnet?',
                    'options' => ['At the poles', 'At the center', 'Uniformly distributed', 'At the edges'],
                    'correct_answer' => 'At the poles',
                    'explanation' => 'The magnetic field is strongest at the poles of a bar magnet.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What happens if you break a bar magnet in half?',
                    'options' => ['You get two complete magnets', 'You get one north pole piece', 'The magnetism is destroyed', 'Nothing happens'],
                    'correct_answer' => 'You get two complete magnets',
                    'explanation' => 'Each piece becomes a complete magnet with its own north and south poles.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Which material can be attracted by a bar magnet?',
                    'options' => ['Iron', 'Wood', 'Plastic', 'Glass'],
                    'correct_answer' => 'Iron',
                    'explanation' => 'Iron is a ferromagnetic material that can be attracted by magnets.',
                ],
            ],

            'Compass Needle' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What does a compass needle align with?',
                    'options' => ['Earth\'s magnetic field', 'Earth\'s rotation', 'Sun\'s position', 'Moon\'s gravity'],
                    'correct_answer' => 'Earth\'s magnetic field',
                    'explanation' => 'A compass needle is a small magnet that aligns itself with Earth\'s magnetic field.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'The north-seeking end of a compass points to Earth\'s geographic North Pole.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'It points to Earth\'s magnetic north pole, which is different from the geographic North Pole.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is a compass needle made of?',
                    'options' => ['Magnetized metal', 'Plastic', 'Glass', 'Wood'],
                    'correct_answer' => 'Magnetized metal',
                    'explanation' => 'A compass needle is made of magnetized metal, usually steel or iron.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What can interfere with a compass needle\'s accuracy?',
                    'options' => ['Nearby magnets or iron objects', 'Nearby wood', 'Nearby plastic', 'Nearby glass'],
                    'correct_answer' => 'Nearby magnets or iron objects',
                    'explanation' => 'Magnetic materials and other magnets can distort the Earth\'s magnetic field locally and affect compass readings.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'In the laboratory, a compass is used to:',
                    'options' => ['Trace magnetic field lines', 'Measure temperature', 'Measure time', 'Measure distance'],
                    'correct_answer' => 'Trace magnetic field lines',
                    'explanation' => 'By moving a compass around a magnet and marking the needle direction, you can trace the magnetic field lines.',
                ],
            ],

            'Ammeter' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What does an ammeter measure?',
                    'options' => ['Electric current', 'Voltage', 'Resistance', 'Power'],
                    'correct_answer' => 'Electric current',
                    'explanation' => 'An ammeter measures electric current flowing through a circuit in Amperes (A).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How should an ammeter be connected in a circuit?',
                    'options' => ['In series', 'In parallel', 'Anywhere', 'Outside the circuit'],
                    'correct_answer' => 'In series',
                    'explanation' => 'An ammeter must be connected in series so that all the current flows through it.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'An ideal ammeter has zero resistance.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'An ideal ammeter has zero resistance so it doesn\'t affect the current it\'s measuring.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the unit of measurement shown on an ammeter?',
                    'options' => ['Ampere (A)', 'Volt (V)', 'Ohm (Ω)', 'Watt (W)'],
                    'correct_answer' => 'Ampere (A)',
                    'explanation' => 'Ammeters display readings in Amperes, the SI unit of electric current.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What happens if you connect an ammeter in parallel?',
                    'options' => ['It may get damaged due to high current', 'It gives accurate reading', 'Nothing happens', 'It measures voltage instead'],
                    'correct_answer' => 'It may get damaged due to high current',
                    'explanation' => 'Connecting in parallel creates a short circuit path, allowing excessive current that can damage the ammeter.',
                ],
            ],

            'Voltmeter' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What does a voltmeter measure?',
                    'options' => ['Potential difference (voltage)', 'Current', 'Resistance', 'Power'],
                    'correct_answer' => 'Potential difference (voltage)',
                    'explanation' => 'A voltmeter measures the electrical potential difference (voltage) between two points in a circuit.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How should a voltmeter be connected in a circuit?',
                    'options' => ['In parallel', 'In series', 'At the end', 'Before the battery'],
                    'correct_answer' => 'In parallel',
                    'explanation' => 'A voltmeter is connected in parallel across the component whose voltage you want to measure.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'An ideal voltmeter has infinite resistance.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'An ideal voltmeter has infinite resistance so no current flows through it, ensuring accurate measurement.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the SI unit of voltage?',
                    'options' => ['Volt (V)', 'Ampere (A)', 'Ohm (Ω)', 'Watt (W)'],
                    'correct_answer' => 'Volt (V)',
                    'explanation' => 'The SI unit of voltage (potential difference) is the Volt (V).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'If a voltmeter is connected in series by mistake, what happens?',
                    'options' => ['Circuit current becomes very small', 'Circuit current increases', 'Voltmeter explodes', 'Nothing happens'],
                    'correct_answer' => 'Circuit current becomes very small',
                    'explanation' => 'Since voltmeters have high resistance, connecting in series drastically reduces the circuit current.',
                ],
            ],

            'Concave Mirror' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of surface does a concave mirror have?',
                    'options' => ['Curved inward (like a cave)', 'Curved outward', 'Flat', 'Wavy'],
                    'correct_answer' => 'Curved inward (like a cave)',
                    'explanation' => 'A concave mirror curves inward, like the inside of a cave (hence "con-CAVE").',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Where do parallel light rays meet after reflecting from a concave mirror?',
                    'options' => ['At the focal point', 'At the center of curvature', 'At infinity', 'They don\'t meet'],
                    'correct_answer' => 'At the focal point',
                    'explanation' => 'Parallel rays reflecting from a concave mirror converge at the focal point.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Concave mirrors are used in vehicle headlights.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Concave mirrors are used in headlights to produce parallel beams of light.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of image is formed when an object is placed very close to a concave mirror?',
                    'options' => ['Virtual, erect, and magnified', 'Real, inverted, and diminished', 'Real, erect, and same size', 'No image'],
                    'correct_answer' => 'Virtual, erect, and magnified',
                    'explanation' => 'When object is between F and P, the image is virtual, erect, and magnified (makeup mirrors).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is another use of concave mirrors?',
                    'options' => ['Shaving/makeup mirrors', 'Rear-view mirrors', 'Shop security mirrors', 'Flat bathroom mirrors'],
                    'correct_answer' => 'Shaving/makeup mirrors',
                    'explanation' => 'Concave mirrors produce magnified images, making them ideal for shaving or applying makeup.',
                ],
            ],

            'Convex Mirror' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of surface does a convex mirror have?',
                    'options' => ['Curved outward', 'Curved inward', 'Flat', 'Irregular'],
                    'correct_answer' => 'Curved outward',
                    'explanation' => 'A convex mirror has a reflecting surface that curves outward.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of image does a convex mirror always form?',
                    'options' => ['Virtual, erect, and diminished', 'Real, inverted, and magnified', 'Virtual, inverted, and same size', 'Real, erect, and magnified'],
                    'correct_answer' => 'Virtual, erect, and diminished',
                    'explanation' => 'Convex mirrors always produce virtual, erect, and diminished images regardless of object position.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Convex mirrors are used as rear-view mirrors in vehicles.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Convex mirrors provide a wider field of view, making them ideal for vehicle rear-view mirrors.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Why are convex mirrors used in shops and parking lots?',
                    'options' => ['They provide a wide field of view', 'They magnify images', 'They are cheaper', 'They are more decorative'],
                    'correct_answer' => 'They provide a wide field of view',
                    'explanation' => 'Convex mirrors can show a large area, making them useful for security and monitoring.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Where is the focal point of a convex mirror located?',
                    'options' => ['Behind the mirror', 'In front of the mirror', 'At the surface', 'At infinity'],
                    'correct_answer' => 'Behind the mirror',
                    'explanation' => 'The focal point of a convex mirror is virtual and located behind the mirror.',
                ],
            ],

            'Glass Prism' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What phenomenon does a glass prism demonstrate when white light passes through it?',
                    'options' => ['Dispersion', 'Reflection', 'Diffraction', 'Polarization'],
                    'correct_answer' => 'Dispersion',
                    'explanation' => 'A prism disperses white light into its component colors (spectrum) due to different wavelengths bending by different amounts.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What are the colors in the visible spectrum produced by a prism (in order)?',
                    'options' => ['Red, Orange, Yellow, Green, Blue, Indigo, Violet (ROYGBIV)', 'Violet, Indigo, Blue, Green, Yellow, Orange, Red', 'Red, Yellow, Blue only', 'White and Black'],
                    'correct_answer' => 'Red, Orange, Yellow, Green, Blue, Indigo, Violet (ROYGBIV)',
                    'explanation' => 'The visible spectrum consists of ROYGBIV - Red, Orange, Yellow, Green, Blue, Indigo, Violet.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Red light bends the most when passing through a prism.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'Violet light bends the most, and red light bends the least because shorter wavelengths refract more.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What causes white light to split into colors in a prism?',
                    'options' => ['Different wavelengths refract by different amounts', 'The prism absorbs some colors', 'The prism creates new colors', 'Magic'],
                    'correct_answer' => 'Different wavelengths refract by different amounts',
                    'explanation' => 'Each color has a different wavelength, and shorter wavelengths bend more than longer ones.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What natural phenomenon demonstrates the same principle as a prism?',
                    'options' => ['Rainbow', 'Lightning', 'Thunder', 'Wind'],
                    'correct_answer' => 'Rainbow',
                    'explanation' => 'Rainbows form when sunlight is dispersed by water droplets, similar to how a prism disperses light.',
                ],
            ],

            'Simple Pendulum' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What factor does NOT affect the period of a simple pendulum?',
                    'options' => ['Mass of the bob', 'Length of string', 'Gravitational acceleration', 'All affect the period'],
                    'correct_answer' => 'Mass of the bob',
                    'explanation' => 'The period of a simple pendulum depends only on its length and gravitational acceleration, not the mass.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What happens to the period if you double the length of a pendulum?',
                    'options' => ['Period increases by √2 times', 'Period doubles', 'Period halves', 'Period stays the same'],
                    'correct_answer' => 'Period increases by √2 times',
                    'explanation' => 'Period is proportional to the square root of length (T ∝ √L), so doubling L increases T by √2.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'A pendulum swings faster at higher altitudes.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'At higher altitudes, gravity is weaker, so the pendulum swings slower (longer period).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the formula for the period of a simple pendulum?',
                    'options' => ['T = 2π√(L/g)', 'T = 2πLg', 'T = L/g', 'T = mg/L'],
                    'correct_answer' => 'T = 2π√(L/g)',
                    'explanation' => 'The period T equals 2π times the square root of length divided by gravitational acceleration.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Simple pendulums can be used to measure:',
                    'options' => ['Gravitational acceleration (g)', 'Electric current', 'Temperature', 'Magnetic field'],
                    'correct_answer' => 'Gravitational acceleration (g)',
                    'explanation' => 'By measuring the period and knowing the length, you can calculate the local gravitational acceleration.',
                ],
            ],

            // ==================== CHEMISTRY ====================
            'Bunsen Burner' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of flame does a Bunsen burner produce when the air hole is fully open?',
                    'options' => ['Blue (non-luminous) flame', 'Yellow (luminous) flame', 'Red flame', 'Green flame'],
                    'correct_answer' => 'Blue (non-luminous) flame',
                    'explanation' => 'With air hole open, complete combustion occurs producing a hot, blue, non-luminous flame.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Which flame is hotter?',
                    'options' => ['Blue flame', 'Yellow flame', 'Both are equal', 'Neither produces heat'],
                    'correct_answer' => 'Blue flame',
                    'explanation' => 'The blue flame is hotter because complete combustion releases more energy.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'The yellow flame produces soot because of incomplete combustion.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'With air hole closed, incomplete combustion produces carbon particles (soot) making the flame yellow.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What gas is typically used in a Bunsen burner?',
                    'options' => ['Natural gas or propane', 'Oxygen', 'Nitrogen', 'Carbon dioxide'],
                    'correct_answer' => 'Natural gas or propane',
                    'explanation' => 'Bunsen burners typically use natural gas (methane) or propane as fuel.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the function of the air hole in a Bunsen burner?',
                    'options' => ['To control the air supply for combustion', 'To release excess gas', 'To cool the flame', 'To change the flame color for decoration'],
                    'correct_answer' => 'To control the air supply for combustion',
                    'explanation' => 'The air hole controls how much air mixes with the gas, affecting combustion completeness.',
                ],
            ],

            'Test Tube' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is a test tube primarily used for in the laboratory?',
                    'options' => ['Holding, mixing, or heating small quantities of chemicals', 'Storing large volumes', 'Measuring precise volumes', 'Filtering solutions'],
                    'correct_answer' => 'Holding, mixing, or heating small quantities of chemicals',
                    'explanation' => 'Test tubes are versatile glassware for handling small amounts of chemicals and observing reactions.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'When heating a test tube, you should point the open end towards people.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'Never point a heating test tube at anyone - contents can suddenly boil and spray out dangerously.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What holds a test tube during heating?',
                    'options' => ['Test tube holder', 'Bare hands', 'Plastic clips', 'Paper towel'],
                    'correct_answer' => 'Test tube holder',
                    'explanation' => 'A test tube holder safely grips the tube to protect hands from heat.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Why are test tubes made of borosilicate glass?',
                    'options' => ['It can withstand heat and thermal shock', 'It\'s cheaper', 'It\'s more colorful', 'It\'s heavier'],
                    'correct_answer' => 'It can withstand heat and thermal shock',
                    'explanation' => 'Borosilicate glass has low thermal expansion, preventing cracking from rapid temperature changes.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How full should a test tube be when heating its contents?',
                    'options' => ['No more than one-third', 'Completely full', 'Half full', 'Empty'],
                    'correct_answer' => 'No more than one-third',
                    'explanation' => 'Filling only one-third prevents boiling liquids from bubbling over or spraying out.',
                ],
            ],

            'Burette' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is a burette primarily used for?',
                    'options' => ['Dispensing precise volumes of liquid in titrations', 'Heating chemicals', 'Storing chemicals', 'Mixing solutions'],
                    'correct_answer' => 'Dispensing precise volumes of liquid in titrations',
                    'explanation' => 'A burette allows controlled delivery of measured liquid volumes, essential for accurate titrations.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What controls the flow of liquid from a burette?',
                    'options' => ['A stopcock (tap)', 'Gravity only', 'A pump', 'Pressure'],
                    'correct_answer' => 'A stopcock (tap)',
                    'explanation' => 'The stopcock at the bottom allows precise control of liquid flow.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Burette readings are taken from the bottom of the meniscus.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'For most liquids, read at the bottom of the curved meniscus for accurate measurement.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Burettes are typically graduated to read:',
                    'options' => ['0.1 mL or 0.05 mL divisions', '10 mL divisions', '100 mL divisions', 'They have no graduations'],
                    'correct_answer' => '0.1 mL or 0.05 mL divisions',
                    'explanation' => 'Burettes have fine graduations (0.1 or 0.05 mL) for precise volume measurement.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'In a titration, the burette typically contains:',
                    'options' => ['The titrant (solution of known concentration)', 'The analyte', 'The indicator', 'Water only'],
                    'correct_answer' => 'The titrant (solution of known concentration)',
                    'explanation' => 'The titrant is added from the burette in controlled amounts to the analyte in the flask.',
                ],
            ],

            'Water Molecule (H2O)' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the chemical formula for water?',
                    'options' => ['H2O', 'HO2', 'H2O2', 'OH'],
                    'correct_answer' => 'H2O',
                    'explanation' => 'Water consists of two hydrogen atoms and one oxygen atom: H2O.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the approximate bond angle in a water molecule?',
                    'options' => ['104.5°', '90°', '180°', '120°'],
                    'correct_answer' => '104.5°',
                    'explanation' => 'The H-O-H bond angle is about 104.5° due to lone pair repulsion on oxygen.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Water molecules have a bent (angular) shape.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Water has a bent molecular geometry due to two bonding pairs and two lone pairs on oxygen.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of bonds hold atoms together within a water molecule?',
                    'options' => ['Covalent bonds', 'Ionic bonds', 'Metallic bonds', 'Van der Waals forces'],
                    'correct_answer' => 'Covalent bonds',
                    'explanation' => 'Hydrogen and oxygen share electrons, forming covalent bonds.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Water is called a "polar" molecule because:',
                    'options' => ['It has unequal charge distribution', 'It comes from the poles', 'It freezes easily', 'It has no charge'],
                    'correct_answer' => 'It has unequal charge distribution',
                    'explanation' => 'Oxygen is more electronegative, creating a partial negative charge on O and positive on H atoms.',
                ],
            ],

            // ==================== BIOLOGY ====================
            'Human Heart' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How many chambers does the human heart have?',
                    'options' => ['Four', 'Two', 'Three', 'Five'],
                    'correct_answer' => 'Four',
                    'explanation' => 'The heart has four chambers: two atria (upper) and two ventricles (lower).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Which chamber pumps blood to the entire body?',
                    'options' => ['Left ventricle', 'Right ventricle', 'Left atrium', 'Right atrium'],
                    'correct_answer' => 'Left ventricle',
                    'explanation' => 'The left ventricle pumps oxygenated blood through the aorta to the body.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'The right side of the heart pumps blood to the lungs.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'The right side receives deoxygenated blood and pumps it to the lungs for oxygenation.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What prevents blood from flowing backward in the heart?',
                    'options' => ['Valves', 'Walls', 'Muscles', 'Nothing'],
                    'correct_answer' => 'Valves',
                    'explanation' => 'Heart valves ensure one-way blood flow and prevent backflow.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the muscular wall between the left and right sides of the heart called?',
                    'options' => ['Septum', 'Valve', 'Atrium', 'Aorta'],
                    'correct_answer' => 'Septum',
                    'explanation' => 'The septum divides the heart into left and right sides, preventing blood mixing.',
                ],
            ],

            'Animal Cell' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the control center of an animal cell?',
                    'options' => ['Nucleus', 'Mitochondria', 'Ribosome', 'Cell membrane'],
                    'correct_answer' => 'Nucleus',
                    'explanation' => 'The nucleus contains DNA and controls cell activities.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Which organelle is called the "powerhouse of the cell"?',
                    'options' => ['Mitochondria', 'Nucleus', 'Golgi apparatus', 'Endoplasmic reticulum'],
                    'correct_answer' => 'Mitochondria',
                    'explanation' => 'Mitochondria produce ATP (energy) through cellular respiration.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Animal cells have a cell wall.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'Animal cells have only a cell membrane, not a cell wall (plant cells have both).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What organelle is responsible for protein synthesis?',
                    'options' => ['Ribosomes', 'Lysosomes', 'Vacuoles', 'Centrioles'],
                    'correct_answer' => 'Ribosomes',
                    'explanation' => 'Ribosomes read mRNA and assemble amino acids into proteins.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What surrounds and protects the contents of an animal cell?',
                    'options' => ['Cell membrane', 'Cell wall', 'Cytoplasm', 'Nucleus'],
                    'correct_answer' => 'Cell membrane',
                    'explanation' => 'The cell membrane is a semi-permeable barrier that controls what enters and leaves the cell.',
                ],
            ],

            'Plant Cell' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What structure is found in plant cells but NOT in animal cells?',
                    'options' => ['Cell wall', 'Nucleus', 'Mitochondria', 'Cell membrane'],
                    'correct_answer' => 'Cell wall',
                    'explanation' => 'Plant cells have a rigid cell wall made of cellulose that animal cells lack.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What organelle carries out photosynthesis?',
                    'options' => ['Chloroplast', 'Mitochondria', 'Ribosome', 'Golgi apparatus'],
                    'correct_answer' => 'Chloroplast',
                    'explanation' => 'Chloroplasts contain chlorophyll and convert light energy into glucose.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Plant cells have a large central vacuole.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Plant cells have a large central vacuole that stores water and maintains turgor pressure.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What gives plants their green color?',
                    'options' => ['Chlorophyll in chloroplasts', 'The cell wall', 'The vacuole', 'The nucleus'],
                    'correct_answer' => 'Chlorophyll in chloroplasts',
                    'explanation' => 'Chlorophyll is a green pigment that absorbs light for photosynthesis.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the function of the large central vacuole in plant cells?',
                    'options' => ['Storage and maintaining cell shape', 'Photosynthesis', 'Protein synthesis', 'DNA storage'],
                    'correct_answer' => 'Storage and maintaining cell shape',
                    'explanation' => 'The vacuole stores water, nutrients, and waste, and helps maintain cell turgor pressure.',
                ],
            ],

            'DNA Double Helix' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the shape of DNA called?',
                    'options' => ['Double helix', 'Single strand', 'Triple helix', 'Square ladder'],
                    'correct_answer' => 'Double helix',
                    'explanation' => 'DNA has a twisted ladder shape called a double helix.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'In DNA, adenine (A) pairs with which base?',
                    'options' => ['Thymine (T)', 'Guanine (G)', 'Cytosine (C)', 'Uracil (U)'],
                    'correct_answer' => 'Thymine (T)',
                    'explanation' => 'In DNA, A pairs with T and G pairs with C (complementary base pairing).',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'DNA contains the genetic instructions for all living organisms.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'DNA carries the genetic code that determines the characteristics of living things.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What makes up the "rungs" of the DNA ladder?',
                    'options' => ['Base pairs (A-T and G-C)', 'Sugar and phosphate', 'Proteins', 'Lipids'],
                    'correct_answer' => 'Base pairs (A-T and G-C)',
                    'explanation' => 'The rungs are formed by hydrogen bonds between complementary base pairs.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What makes up the "backbone" of the DNA molecule?',
                    'options' => ['Sugar-phosphate groups', 'Base pairs', 'Proteins', 'Hydrogen bonds'],
                    'correct_answer' => 'Sugar-phosphate groups',
                    'explanation' => 'The sides of the DNA ladder are made of alternating sugar and phosphate molecules.',
                ],
            ],

            'Microscope' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Which part of the microscope do you look through?',
                    'options' => ['Eyepiece (ocular lens)', 'Objective lens', 'Stage', 'Base'],
                    'correct_answer' => 'Eyepiece (ocular lens)',
                    'explanation' => 'The eyepiece is the lens at the top that you look through.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'If the eyepiece is 10x and objective lens is 40x, what is the total magnification?',
                    'options' => ['400x', '50x', '10x', '40x'],
                    'correct_answer' => '400x',
                    'explanation' => 'Total magnification = eyepiece × objective = 10 × 40 = 400x.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'You should always start with the highest power objective lens.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'Always start with the lowest power objective to find and focus on the specimen first.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the function of the stage on a microscope?',
                    'options' => ['To hold the slide with the specimen', 'To provide light', 'To focus the image', 'To magnify the image'],
                    'correct_answer' => 'To hold the slide with the specimen',
                    'explanation' => 'The stage is the flat platform where you place your specimen slide.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Which knob is used for fine focusing?',
                    'options' => ['Fine adjustment knob', 'Coarse adjustment knob', 'Stage control', 'Light switch'],
                    'correct_answer' => 'Fine adjustment knob',
                    'explanation' => 'The fine adjustment knob makes small, precise focus adjustments especially at high magnification.',
                ],
            ],

            // ==================== MATHEMATICS ====================
            'Geometric Solids Set' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How many faces does a cube have?',
                    'options' => ['6', '4', '8', '12'],
                    'correct_answer' => '6',
                    'explanation' => 'A cube has 6 square faces.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the formula for the volume of a cylinder?',
                    'options' => ['V = πr²h', 'V = lwh', 'V = (4/3)πr³', 'V = (1/3)πr²h'],
                    'correct_answer' => 'V = πr²h',
                    'explanation' => 'Volume of a cylinder = π × radius² × height.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'A sphere has no edges or vertices.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'A sphere is perfectly round with no flat faces, edges, or vertices.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the formula for the volume of a cone?',
                    'options' => ['V = (1/3)πr²h', 'V = πr²h', 'V = (4/3)πr³', 'V = lwh'],
                    'correct_answer' => 'V = (1/3)πr²h',
                    'explanation' => 'Volume of a cone = (1/3) × π × radius² × height.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How many vertices does a triangular pyramid (tetrahedron) have?',
                    'options' => ['4', '3', '5', '6'],
                    'correct_answer' => '4',
                    'explanation' => 'A tetrahedron has 4 vertices, 4 triangular faces, and 6 edges.',
                ],
            ],

            'Pythagorean Theorem Model' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What does the Pythagorean theorem state?',
                    'options' => ['a² + b² = c²', 'a + b = c', 'a × b = c', 'a² - b² = c²'],
                    'correct_answer' => 'a² + b² = c²',
                    'explanation' => 'In a right triangle, the square of the hypotenuse equals the sum of squares of the other two sides.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'In the Pythagorean theorem, what is "c"?',
                    'options' => ['The hypotenuse (longest side)', 'The shortest side', 'Any side', 'The right angle'],
                    'correct_answer' => 'The hypotenuse (longest side)',
                    'explanation' => 'c represents the hypotenuse, the side opposite the right angle.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'The Pythagorean theorem only works for right triangles.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'The theorem applies specifically to right-angled triangles.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'If a = 3 and b = 4, what is c?',
                    'options' => ['5', '7', '12', '25'],
                    'correct_answer' => '5',
                    'explanation' => '3² + 4² = 9 + 16 = 25 = 5². So c = 5 (the famous 3-4-5 triangle).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is a "Pythagorean triple"?',
                    'options' => ['Three whole numbers that satisfy a² + b² = c²', 'Three angles in a triangle', 'Three types of triangles', 'Three mathematicians'],
                    'correct_answer' => 'Three whole numbers that satisfy a² + b² = c²',
                    'explanation' => 'Examples include (3,4,5), (5,12,13), and (8,15,17).',
                ],
            ],

            // ==================== ENGINEERING ====================
            'Gear System' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the gear ratio if the driver gear has 20 teeth and driven gear has 40 teeth?',
                    'options' => ['1:2', '2:1', '1:1', '20:40'],
                    'correct_answer' => '1:2',
                    'explanation' => 'Gear ratio = driver teeth : driven teeth = 20:40 = 1:2.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'If a small gear drives a large gear, what happens to the speed?',
                    'options' => ['Speed decreases', 'Speed increases', 'Speed stays the same', 'Gears stop'],
                    'correct_answer' => 'Speed decreases',
                    'explanation' => 'A small gear driving a large gear reduces speed but increases torque.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Two meshing gears rotate in the same direction.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'False',
                    'explanation' => 'Meshing gears rotate in opposite directions.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What do gears help transmit?',
                    'options' => ['Motion and force', 'Heat', 'Light', 'Sound'],
                    'correct_answer' => 'Motion and force',
                    'explanation' => 'Gears transfer rotational motion and torque between shafts.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of gear changes rotational motion by 90 degrees?',
                    'options' => ['Bevel gear', 'Spur gear', 'Worm gear', 'Rack and pinion'],
                    'correct_answer' => 'Bevel gear',
                    'explanation' => 'Bevel gears have angled teeth that transmit motion between perpendicular shafts.',
                ],
            ],

            'Pulley System' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What does a single fixed pulley do?',
                    'options' => ['Changes the direction of force', 'Reduces the force needed', 'Increases speed', 'None of the above'],
                    'correct_answer' => 'Changes the direction of force',
                    'explanation' => 'A single fixed pulley only changes direction; it doesn\'t reduce the force needed.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'If a pulley system has a mechanical advantage of 4, how much force is needed to lift 100N?',
                    'options' => ['25N', '100N', '400N', '50N'],
                    'correct_answer' => '25N',
                    'explanation' => 'Force needed = Load / MA = 100N / 4 = 25N.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'A movable pulley provides a mechanical advantage of 2.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'A single movable pulley halves the force needed (MA = 2).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the trade-off when using multiple pulleys to reduce force?',
                    'options' => ['You must pull more rope', 'The load gets heavier', 'The pulleys break', 'Nothing'],
                    'correct_answer' => 'You must pull more rope',
                    'explanation' => 'While less force is needed, you must pull the rope a greater distance.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of simple machine is a pulley?',
                    'options' => ['Wheel and axle (modified lever)', 'Inclined plane', 'Screw', 'Wedge'],
                    'correct_answer' => 'Wheel and axle (modified lever)',
                    'explanation' => 'A pulley is a modified wheel and axle that uses a rope to change force direction.',
                ],
            ],

            'Lever Types' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'In a first-class lever, where is the fulcrum located?',
                    'options' => ['Between the effort and load', 'At one end', 'At the load', 'There is no fulcrum'],
                    'correct_answer' => 'Between the effort and load',
                    'explanation' => 'First-class levers have the fulcrum between effort and load (like a seesaw).',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'A wheelbarrow is an example of which class of lever?',
                    'options' => ['Second-class', 'First-class', 'Third-class', 'Fourth-class'],
                    'correct_answer' => 'Second-class',
                    'explanation' => 'In second-class levers, the load is between the fulcrum and effort.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'A fishing rod is an example of a third-class lever.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'In third-class levers, the effort is between the fulcrum and load.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the fixed point around which a lever rotates called?',
                    'options' => ['Fulcrum', 'Effort', 'Load', 'Pivot arm'],
                    'correct_answer' => 'Fulcrum',
                    'explanation' => 'The fulcrum is the pivot point of a lever.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'Which class of lever always has a mechanical advantage greater than 1?',
                    'options' => ['Second-class lever', 'First-class lever', 'Third-class lever', 'All classes'],
                    'correct_answer' => 'Second-class lever',
                    'explanation' => 'Second-class levers always multiply force because load is closer to fulcrum than effort.',
                ],
            ],

            // ==================== ROBOTICS ====================
            'Robot Arm' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What does "6-axis" mean for a robotic arm?',
                    'options' => ['It has 6 joints/degrees of freedom', 'It has 6 motors', 'It can lift 6 kg', 'It is 6 meters long'],
                    'correct_answer' => 'It has 6 joints/degrees of freedom',
                    'explanation' => '6-axis means the arm can move in 6 independent directions or rotations.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the gripper at the end of a robot arm called?',
                    'options' => ['End effector', 'Motor', 'Sensor', 'Controller'],
                    'correct_answer' => 'End effector',
                    'explanation' => 'The end effector is the tool or gripper at the end that interacts with objects.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Robot arms are commonly used in manufacturing for tasks like welding and assembly.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Industrial robot arms perform repetitive tasks with high precision in factories.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What type of motor is commonly used in robot arm joints?',
                    'options' => ['Servo motor', 'Diesel engine', 'Steam engine', 'Water wheel'],
                    'correct_answer' => 'Servo motor',
                    'explanation' => 'Servo motors provide precise position control needed for robot arm movements.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'The study of robot motion and positioning is called:',
                    'options' => ['Kinematics', 'Biology', 'Geology', 'Psychology'],
                    'correct_answer' => 'Kinematics',
                    'explanation' => 'Robot kinematics deals with the geometry of robot motion without considering forces.',
                ],
            ],

            'Arduino Board' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is Arduino?',
                    'options' => ['A microcontroller platform for electronics projects', 'A video game', 'A type of robot', 'A programming language only'],
                    'correct_answer' => 'A microcontroller platform for electronics projects',
                    'explanation' => 'Arduino is an open-source electronics platform with easy-to-use hardware and software.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What programming language is used to program Arduino?',
                    'options' => ['C/C++', 'Python only', 'Java only', 'HTML'],
                    'correct_answer' => 'C/C++',
                    'explanation' => 'Arduino uses a simplified version of C/C++ programming language.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Arduino can control LEDs, motors, and sensors.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Arduino can interface with many electronic components through its digital and analog pins.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What are the two main functions in every Arduino program?',
                    'options' => ['setup() and loop()', 'start() and stop()', 'begin() and end()', 'main() and run()'],
                    'correct_answer' => 'setup() and loop()',
                    'explanation' => 'setup() runs once at start, loop() runs continuously.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What voltage do most Arduino boards operate at?',
                    'options' => ['5V', '220V', '12V', '1000V'],
                    'correct_answer' => '5V',
                    'explanation' => 'Most Arduino boards operate at 5V DC, making them safe for beginners.',
                ],
            ],

            'Servo Motor' => [
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is a servo motor primarily used for?',
                    'options' => ['Precise position control', 'Continuous high-speed rotation', 'Generating electricity', 'Heating'],
                    'correct_answer' => 'Precise position control',
                    'explanation' => 'Servo motors excel at moving to and holding specific angular positions.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What is the typical rotation range of a standard servo motor?',
                    'options' => ['0-180 degrees', '0-360 degrees continuous', '0-90 degrees', '0-45 degrees'],
                    'correct_answer' => '0-180 degrees',
                    'explanation' => 'Standard hobby servos typically rotate between 0 and 180 degrees.',
                ],
                [
                    'question_type' => 'true_false',
                    'question_text' => 'Servo motors contain internal feedback mechanisms for position control.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'Servos use internal potentiometers or encoders to know their current position.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'How is a servo motor controlled?',
                    'options' => ['PWM (Pulse Width Modulation) signals', 'AC voltage', 'Direct current only', 'Radio waves'],
                    'correct_answer' => 'PWM (Pulse Width Modulation) signals',
                    'explanation' => 'The pulse width of the control signal determines the servo\'s position.',
                ],
                [
                    'question_type' => 'mcq',
                    'question_text' => 'What components are inside a servo motor?',
                    'options' => ['DC motor, gears, and control circuit', 'Just a coil of wire', 'Only magnets', 'Steam and water'],
                    'correct_answer' => 'DC motor, gears, and control circuit',
                    'explanation' => 'Servos combine a motor with gear reduction and a feedback control system.',
                ],
            ],
        ];

        // Get all models from database
        $models = Model3d::all();

        foreach ($models as $model) {
            if (isset($questionsData[$model->name])) {
                $order = 1;
                foreach ($questionsData[$model->name] as $questionData) {
                    ModelQuestion::updateOrCreate(
                        [
                            'model_id' => $model->id,
                            'question_text' => $questionData['question_text'],
                        ],
                        [
                            'model_id' => $model->id,
                            'question_type' => $questionData['question_type'],
                            'question_text' => $questionData['question_text'],
                            'options' => $questionData['options'],
                            'correct_answer' => $questionData['correct_answer'],
                            'explanation' => $questionData['explanation'],
                            'order' => $order++,
                        ]
                    );
                }
            }
        }
    }
}



