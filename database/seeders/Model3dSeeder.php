<?php

namespace Database\Seeders;

use App\Models\Model3d;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class Model3dSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::pluck('id', 'subject_name')->toArray();

        $models = [
            // ==================== PHYSICS ====================
            [
                'subject' => 'Physics',
                'name' => 'Spring Balance',
                'description' => 'A spring balance (or Newton meter) is used to measure force or weight. It works on the principle of Hooke\'s Law - the extension of the spring is proportional to the applied force.',
                'tags' => 'force, weight, measurement, hookes law, mechanics',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Eureka Can (Overflow Can)',
                'description' => 'Used in Archimedes\' Principle experiments to measure the volume of water displaced by an object. Essential for understanding buoyancy and flotation.',
                'tags' => 'archimedes, buoyancy, displacement, volume, flotation',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Bar Magnet',
                'description' => 'A permanent magnet with north and south poles. Used to demonstrate magnetic field lines, attraction, repulsion, and the Earth\'s magnetic field.',
                'tags' => 'magnetism, magnetic field, poles, attraction, repulsion',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Compass Needle',
                'description' => 'A magnetized needle that aligns with Earth\'s magnetic field. Used to trace magnetic field lines and demonstrate magnetic properties.',
                'tags' => 'magnetism, direction, navigation, magnetic field',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Ammeter',
                'description' => 'An instrument used to measure electric current in amperes. Connected in series in a circuit to measure current flow.',
                'tags' => 'electricity, current, measurement, circuit, amperes',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Voltmeter',
                'description' => 'An instrument used to measure electrical potential difference (voltage) between two points. Connected in parallel across components.',
                'tags' => 'electricity, voltage, potential difference, measurement',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Electric Circuit Components',
                'description' => 'Basic circuit components including battery, switch, resistor, and connecting wires. Used to build series and parallel circuits.',
                'tags' => 'electricity, circuit, battery, switch, resistor, series, parallel',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Concave Mirror',
                'description' => 'A curved mirror with reflecting surface curved inward. Used to study reflection, focal length, and image formation in optics.',
                'tags' => 'optics, light, reflection, focal length, mirror, image',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Convex Mirror',
                'description' => 'A curved mirror with reflecting surface curved outward. Always forms virtual, diminished images. Used in vehicle rear-view mirrors.',
                'tags' => 'optics, light, reflection, mirror, virtual image',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Glass Prism',
                'description' => 'A triangular glass block that disperses white light into its component colors (spectrum). Demonstrates refraction and dispersion.',
                'tags' => 'optics, light, refraction, dispersion, spectrum, rainbow',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Simple Pendulum',
                'description' => 'A weight (bob) suspended from a string that swings freely. Used to study periodic motion and measure gravitational acceleration.',
                'tags' => 'oscillation, period, gravity, mechanics, pendulum',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Ripple Tank',
                'description' => 'A shallow tank of water used to demonstrate wave behavior including reflection, refraction, diffraction, and interference.',
                'tags' => 'waves, reflection, refraction, diffraction, interference',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Ball and Ring Apparatus',
                'description' => 'Demonstrates thermal expansion of solids. The ball passes through the ring when cold but not when heated.',
                'tags' => 'thermal expansion, heat, solids, temperature',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Solenoid',
                'description' => 'A coil of wire that produces a magnetic field when electric current passes through it. Foundation of electromagnets.',
                'tags' => 'electromagnetism, magnetic field, coil, current',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Galvanometer',
                'description' => 'A sensitive instrument for detecting and measuring small electric currents. Used in electromagnetic induction experiments.',
                'tags' => 'electricity, current, measurement, induction',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Diode',
                'description' => 'A semiconductor device that allows current to flow in one direction only. Used in rectifier circuits to convert AC to DC.',
                'tags' => 'electronics, semiconductor, rectifier, AC, DC',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Physics',
                'name' => 'Transistor',
                'description' => 'A semiconductor device used to amplify or switch electronic signals. The building block of modern electronics.',
                'tags' => 'electronics, semiconductor, amplifier, switch',
                'file' => null,
                'thumbnail' => null,
            ],

            // ==================== CHEMISTRY ====================
            [
                'subject' => 'Chemistry',
                'name' => 'Bunsen Burner',
                'description' => 'A gas burner producing a single open flame used for heating, sterilization, and combustion in laboratory experiments.',
                'tags' => 'heating, flame, combustion, laboratory, gas',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Chemistry',
                'name' => 'Erlenmeyer Flask',
                'description' => 'A conical flask with a flat bottom and narrow neck. Used for mixing, heating, and storing chemical solutions.',
                'tags' => 'flask, container, mixing, laboratory, glassware',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Chemistry',
                'name' => 'Test Tube',
                'description' => 'A cylindrical glass tube closed at one end. Used to hold, mix, or heat small quantities of chemicals.',
                'tags' => 'tube, container, reactions, laboratory, glassware',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Chemistry',
                'name' => 'Burette',
                'description' => 'A graduated glass tube with a tap used to deliver measured volumes of liquid, especially in titrations.',
                'tags' => 'titration, measurement, volume, precision, laboratory',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Chemistry',
                'name' => 'Atom Model - Hydrogen',
                'description' => 'A 3D representation of hydrogen atom showing one proton and one electron. The simplest atom in the universe.',
                'tags' => 'atom, hydrogen, electron, proton, atomic structure',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Chemistry',
                'name' => 'Water Molecule (H2O)',
                'description' => 'A 3D model showing the molecular structure of water - two hydrogen atoms bonded to one oxygen atom at 104.5° angle.',
                'tags' => 'molecule, water, hydrogen, oxygen, bonding',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Chemistry',
                'name' => 'Periodic Table Element Cube',
                'description' => 'Interactive 3D cubes representing periodic table elements with atomic number, mass, and electron configuration.',
                'tags' => 'periodic table, elements, atomic number, chemistry',
                'file' => null,
                'thumbnail' => null,
            ],

            // ==================== BIOLOGY ====================
            [
                'subject' => 'Biology',
                'name' => 'Human Heart',
                'description' => 'A detailed 3D model of the human heart showing all four chambers, valves, and major blood vessels. Learn about blood circulation.',
                'tags' => 'heart, circulation, anatomy, cardiovascular, organs',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Biology',
                'name' => 'Animal Cell',
                'description' => 'A 3D model of an animal cell showing nucleus, mitochondria, endoplasmic reticulum, Golgi apparatus, and other organelles.',
                'tags' => 'cell, organelles, nucleus, mitochondria, cytoplasm',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Biology',
                'name' => 'Plant Cell',
                'description' => 'A 3D model of a plant cell showing cell wall, chloroplasts, large vacuole, and other unique plant cell structures.',
                'tags' => 'cell, plant, chloroplast, cell wall, vacuole',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Biology',
                'name' => 'DNA Double Helix',
                'description' => 'A 3D model of DNA structure showing the double helix, base pairs (A-T, G-C), sugar-phosphate backbone, and nucleotides.',
                'tags' => 'DNA, genetics, helix, nucleotides, heredity',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Biology',
                'name' => 'Human Eye',
                'description' => 'Cross-section of the human eye showing cornea, lens, retina, optic nerve, and how light is focused to form images.',
                'tags' => 'eye, vision, anatomy, lens, retina, optics',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Biology',
                'name' => 'Human Skeleton',
                'description' => 'A complete 3D model of the human skeletal system with 206 bones. Learn bone names and their functions.',
                'tags' => 'skeleton, bones, anatomy, skull, spine, joints',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Biology',
                'name' => 'Microscope',
                'description' => 'A compound light microscope model showing eyepiece, objective lenses, stage, and light source. Essential biology tool.',
                'tags' => 'microscope, magnification, laboratory, observation',
                'file' => null,
                'thumbnail' => null,
            ],

            // ==================== MATHEMATICS ====================
            [
                'subject' => 'Mathematics',
                'name' => 'Geometric Solids Set',
                'description' => 'A collection of 3D geometric shapes: cube, sphere, cylinder, cone, pyramid, prism. Study volume and surface area.',
                'tags' => 'geometry, 3D shapes, volume, surface area, solids',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Mathematics',
                'name' => '3D Coordinate System',
                'description' => 'An interactive 3D Cartesian coordinate system showing X, Y, Z axes. Plot points and visualize 3D space.',
                'tags' => 'coordinates, axes, 3D space, cartesian, graphing',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Mathematics',
                'name' => 'Pythagorean Theorem Model',
                'description' => 'Visual representation of a² + b² = c² using squares on each side of a right triangle.',
                'tags' => 'pythagoras, triangle, theorem, geometry, proof',
                'file' => null,
                'thumbnail' => null,
            ],

            // ==================== ENGINEERING ====================
            [
                'subject' => 'Engineering',
                'name' => 'Gear System',
                'description' => 'A system of interlocking gears demonstrating mechanical advantage, gear ratios, and power transmission.',
                'tags' => 'gears, mechanical, transmission, ratio, torque',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Engineering',
                'name' => 'Pulley System',
                'description' => 'Single and compound pulley systems showing mechanical advantage in lifting heavy loads with less force.',
                'tags' => 'pulley, mechanical advantage, force, lifting, simple machine',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Engineering',
                'name' => 'Lever Types',
                'description' => 'Three classes of levers showing fulcrum, effort, and load positions. Understand mechanical advantage.',
                'tags' => 'lever, fulcrum, force, simple machine, mechanical',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Engineering',
                'name' => 'Electric Motor',
                'description' => 'A DC electric motor showing armature, commutator, brushes, and permanent magnets. Converts electrical to mechanical energy.',
                'tags' => 'motor, electricity, magnetism, rotation, energy',
                'file' => null,
                'thumbnail' => null,
            ],

            // ==================== CODING ====================
            [
                'subject' => 'Coding',
                'name' => 'Computer Motherboard',
                'description' => 'A detailed 3D model of a computer motherboard showing CPU socket, RAM slots, expansion slots, and chipsets.',
                'tags' => 'computer, motherboard, hardware, CPU, components',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Coding',
                'name' => 'Binary Counter',
                'description' => 'An interactive model demonstrating binary number system (0s and 1s) - the foundation of all computer operations.',
                'tags' => 'binary, numbers, computer science, digital, bits',
                'file' => null,
                'thumbnail' => null,
            ],

            // ==================== ROBOTICS ====================
            [
                'subject' => 'Robotics',
                'name' => 'Robot Arm',
                'description' => 'A 6-axis robotic arm showing joints, motors, and end effector. Learn about degrees of freedom and robot kinematics.',
                'tags' => 'robot, arm, joints, motors, kinematics, automation',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Robotics',
                'name' => 'Ultrasonic Sensor',
                'description' => 'An ultrasonic distance sensor (like HC-SR04) showing how robots detect obstacles using sound waves.',
                'tags' => 'sensor, ultrasonic, distance, obstacle detection, arduino',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Robotics',
                'name' => 'Servo Motor',
                'description' => 'A servo motor showing internal gears and control mechanism. Used for precise angular positioning in robots.',
                'tags' => 'servo, motor, control, positioning, actuator',
                'file' => null,
                'thumbnail' => null,
            ],
            [
                'subject' => 'Robotics',
                'name' => 'Arduino Board',
                'description' => 'An Arduino microcontroller board - the brain of many robotics projects. Shows pins, ports, and components.',
                'tags' => 'arduino, microcontroller, programming, electronics',
                'file' => null,
                'thumbnail' => null,
            ],
        ];

        foreach ($models as $model) {
            if (isset($subjects[$model['subject']])) {
                Model3d::updateOrCreate(
                    [
                        'name' => $model['name'],
                        'subject_id' => $subjects[$model['subject']],
                    ],
                    [
                        'subject_id' => $subjects[$model['subject']],
                        'name' => $model['name'],
                        'description' => $model['description'],
                        'tags' => $model['tags'],
                        'file' => $model['file'],
                        'thumbnail' => $model['thumbnail'],
                    ]
                );
            }
        }
    }
}

