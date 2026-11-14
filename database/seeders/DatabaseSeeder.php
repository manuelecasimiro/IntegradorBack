<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventAccessibility;
use App\Models\EventVolunteer;
use App\Models\Message;
use App\Models\Review;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\VolunteerProfile;
use App\Models\VolunteerRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@inovaacess.com.br')->exists()) {
            $this->command?->info('Base de dados já populada anteriormente com os dados iniciais.');
            return;
        }

        $defaultPassword = Hash::make('password123');

        // ==========================================
        // 1. DONO DO SERVIÇO & ADMINISTRADOR GERAL
        // ==========================================
        $admin = User::create([
            'name' => 'Alexandre Guimarães (Dono do Serviço / Admin)',
            'email' => 'admin@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'admin',
            'cpf' => '000.000.000-01',
            'phone' => '(21) 98888-0001',
            'profile_photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $admin->id,
            'bio' => 'Fundador e Administrador Geral da plataforma Inova Acess. Responsável pela governança, concessão de credenciais a organizadores e mediação da rede solidária.',
        ]);

        // ==========================================
        // 2. ORGANIZADORES DE EVENTOS CREDENCIADOS
        // ==========================================
        $org1 = User::create([
            'name' => 'Produtora Arte & Inclusão',
            'email' => 'organizador@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'organizer',
            'cpf' => '000.000.000-02',
            'phone' => '(21) 98888-0002',
            'profile_photo' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=300&h=300&fit=crop',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $org1->id,
            'bio' => 'Produtora cultural especializada em festivais de música popular e teatro acessível em todo o território nacional.',
        ]);

        $org2 = User::create([
            'name' => 'Brasil Entretenimento Acessível',
            'email' => 'brasil.eventos@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'organizer',
            'cpf' => '000.000.000-03',
            'phone' => '(11) 97777-0003',
            'profile_photo' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=300&h=300&fit=crop',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $org2->id,
            'bio' => 'Empresa organizadora de conferências, congressos e feiras industriais alinhadas aos padrões de sustentabilidade e acessibilidade do SENAI/Firjan.',
        ]);

        // ==========================================
        // 3. VOLUNTÁRIOS SOLIDÁRIOS CAPACITADOS
        // ==========================================
        $vol1 = User::create([
            'name' => 'Juliana Santos',
            'email' => 'juliana.voluntaria@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'volunteer',
            'cpf' => '111.222.333-44',
            'phone' => '(21) 99111-2233',
            'profile_photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        VolunteerProfile::create([
            'user_id' => $vol1->id,
            'biography' => 'Intérprete de Libras certificada (Prolibras) e voluntária em eventos culturais há mais de 4 anos. Apaixonada por quebrar as barreiras de comunicação no entretenimento.',
            'linkedin_url' => 'https://linkedin.com/in/juliana-libras',
            'availability_status' => 'available',
            'experience' => '4 anos atuando em espetáculos teatrais e shows musicais no Rio de Janeiro e São Paulo.',
            'skills' => ['Libras', 'Acolhimento', 'Orientação', 'Comunicação Inclusiva'],
            'rating_avg' => 5.00,
            'completed_services_count' => 16,
        ]);

        $vol2 = User::create([
            'name' => 'Carlos Eduardo Oliveira',
            'email' => 'carlos.voluntario@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'volunteer',
            'cpf' => '222.333.444-55',
            'phone' => '(11) 99222-3344',
            'profile_photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        VolunteerProfile::create([
            'user_id' => $vol2->id,
            'biography' => 'Fisioterapeuta e guia vidente capacitado para acompanhamento de pessoas com deficiência visual e mobilidade reduzida em feiras e grandes festivais.',
            'linkedin_url' => 'https://linkedin.com/in/carlos-fisio',
            'availability_status' => 'available',
            'experience' => '3 anos voluntariando no Comitê Paralímpico Brasileiro e feiras de negócios.',
            'skills' => ['Mobilidade e Acompanhamento', 'Guia Vidente', 'Acolhimento', 'Orientação'],
            'rating_avg' => 4.90,
            'completed_services_count' => 11,
        ]);

        $vol3 = User::create([
            'name' => 'Mariana Costa',
            'email' => 'mariana.voluntaria@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'volunteer',
            'cpf' => '333.444.555-66',
            'phone' => '(31) 99333-4455',
            'profile_photo' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        VolunteerProfile::create([
            'user_id' => $vol3->id,
            'biography' => 'Fonoaudióloga e especialista em audiodescrição de apoio. Dedica-se a tornar os espetáculos compreensíveis e ricos em detalhes narrativos para pessoas cegas.',
            'linkedin_url' => 'https://linkedin.com/in/mariana-costa',
            'availability_status' => 'available',
            'experience' => '2 anos de vivência em eventos esportivos, literários e exibições audiovisuais.',
            'skills' => ['Audiodescrição de Apoio', 'Acolhimento', 'Comunicação Inclusiva', 'Orientação'],
            'rating_avg' => 4.85,
            'completed_services_count' => 8,
        ]);

        $vol4 = User::create([
            'name' => 'Roberto Mendes',
            'email' => 'roberto.voluntario@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'volunteer',
            'cpf' => '444.555.777-99',
            'phone' => '(41) 99444-8899',
            'profile_photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        VolunteerProfile::create([
            'user_id' => $vol4->id,
            'biography' => 'Educador e especialista em suporte a pessoas neurodivergentes (TEA/TDAH). Capacitado em condução para áreas de descompressão e empréstimo de kits sensoriais.',
            'linkedin_url' => 'https://linkedin.com/in/roberto-mendes',
            'availability_status' => 'available',
            'experience' => '3 anos em eventos acadêmicos e mostras culturais com espaços sensoriais reservados.',
            'skills' => ['Acolhimento', 'Orientação', 'Comunicação Inclusiva', 'Apoio Neurodivergentes'],
            'rating_avg' => 5.00,
            'completed_services_count' => 7,
        ]);

        $vol5 = User::create([
            'name' => 'Camila Rocha',
            'email' => 'camila.voluntaria@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'volunteer',
            'cpf' => '555.666.888-00',
            'phone' => '(71) 99555-1234',
            'profile_photo' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        VolunteerProfile::create([
            'user_id' => $vol5->id,
            'biography' => 'Pedagoga bilíngue (Libras/Português) e entusiasta da acessibilidade cultural em festivais ao ar livre.',
            'linkedin_url' => 'https://linkedin.com/in/camila-rocha',
            'availability_status' => 'busy',
            'experience' => 'Mais de 5 eventos de grande porte acompanhando participantes surdos.',
            'skills' => ['Libras', 'Acolhimento', 'Orientação'],
            'rating_avg' => 4.95,
            'completed_services_count' => 12,
        ]);

        // ==========================================
        // 4. CLIENTES COM DEFICIÊNCIA (PCDs)
        // ==========================================
        $client1 = User::create([
            'name' => 'Lucas Ferreira',
            'email' => 'lucas.cliente@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'client',
            'cpf' => '444.555.666-77',
            'phone' => '(21) 99444-5566',
            'profile_photo' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $client1->id,
            'bio' => 'Estudante de engenharia e apaixonado por música ao vivo.',
            'accessibility_needs' => 'Cadeirante. Necessito de apoio para locomoção em rampas íngremes e localização de banheiros adaptados.',
            'emergency_contact_name' => 'Marcos Ferreira (Irmão)',
            'emergency_contact_phone' => '(21) 98777-6655',
        ]);

        $client2 = User::create([
            'name' => 'Beatriz Lima',
            'email' => 'beatriz.cliente@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'client',
            'cpf' => '555.666.777-88',
            'phone' => '(11) 99555-6677',
            'profile_photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $client2->id,
            'bio' => 'Designer gráfica e amante de espetáculos teatrais.',
            'accessibility_needs' => 'Deficiência auditiva severa. Busco voluntário com conhecimento em Libras para acompanhamento nas palestras e espetáculos.',
            'emergency_contact_name' => 'Fernanda Lima (Mãe)',
            'emergency_contact_phone' => '(11) 98666-5544',
        ]);

        $client3 = User::create([
            'name' => 'André Souza',
            'email' => 'andre.cliente@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'client',
            'cpf' => '666.777.888-99',
            'phone' => '(31) 99666-7788',
            'profile_photo' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $client3->id,
            'bio' => 'Professor de história, deficiente visual e fã de festivais de literatura e gastronomia.',
            'accessibility_needs' => 'Deficiência visual total (Cego). Necessito de guia vidente para circulação segura e suporte de audiodescrição.',
            'emergency_contact_name' => 'Cláudia Souza (Esposa)',
            'emergency_contact_phone' => '(31) 98555-4433',
        ]);

        $client4 = User::create([
            'name' => 'Fernanda Martins',
            'email' => 'fernanda.cliente@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'client',
            'cpf' => '777.888.999-11',
            'phone' => '(41) 99777-8822',
            'profile_photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $client4->id,
            'bio' => 'Pessoa no Espectro Autista (TEA). Adora tecnologia, robótica e exposições científicas.',
            'accessibility_needs' => 'Sensibilidade auditiva a ruídos intensos (TEA). Preciso de apoio para identificar áreas silenciosas de descompressão no pavilhão.',
            'emergency_contact_name' => 'Paulo Martins (Pai)',
            'emergency_contact_phone' => '(41) 98444-3322',
        ]);

        $client5 = User::create([
            'name' => 'Thiago Ribeiro',
            'email' => 'thiago.cliente@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'client',
            'cpf' => '888.999.000-22',
            'phone' => '(71) 99888-9911',
            'profile_photo' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=300&h=300&fit=crop&crop=face',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $client5->id,
            'bio' => 'Aposentado, mobilidade reduzida em decorrência de cirurgia no joelho.',
            'accessibility_needs' => 'Uso bengala para caminhar. Preciso de suporte em pisos escorregadios e degraus sem corrimão.',
            'emergency_contact_name' => 'Mariana Ribeiro (Filha)',
            'emergency_contact_phone' => '(71) 98333-2211',
        ]);

        // ==========================================
        // 5. USUÁRIO BANIDO / DESATIVADO (TESTE DE BANIMENTO)
        // ==========================================
        $bannedUser = User::create([
            'name' => 'Marcos Silva (Conta Banida por Infração)',
            'email' => 'usuario.infrator@inovaacess.com.br',
            'password' => $defaultPassword,
            'role' => 'volunteer',
            'cpf' => '999.000.111-33',
            'phone' => '(11) 99999-0000',
            'profile_photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop',
            'is_active' => false, // CONTA BANIDA
        ]);

        VolunteerProfile::create([
            'user_id' => $bannedUser->id,
            'biography' => 'Conta suspensa temporariamente pela moderação por descumprimento das diretrizes de acolhimento.',
            'availability_status' => 'unavailable',
            'rating_avg' => 2.10,
            'completed_services_count' => 1,
        ]);

        // ==========================================
        // 6. EVENTOS COM AUDITORIA DE ACESSIBILIDADE
        // ==========================================
        $event1 = Event::create([
            'organizer_id' => $org1->id,
            'name' => 'Festival Som & Luz 2026',
            'description' => 'Um dos maiores festivais de música popular brasileira, com palco principal adaptado, área reservada para cadeirantes, intérpretes de Libras em todas as apresentações e equipe de apoio.',
            'location' => 'Parque dos Atletas',
            'address' => 'Av. Salvador Allende, 6500 - Barra da Tijuca',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'event_date' => now()->addDays(12)->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '23:30:00',
            'ticket_information' => 'Ingresso PCD com direito a acompanhante conforme Lei 12.933/2013.',
            'banner_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=800&h=450&fit=crop',
            'status' => 'published',
        ]);

        EventAccessibility::create([
            'event_id' => $event1->id,
            'ramps' => true,
            'accessible_restrooms' => true,
            'reserved_spaces' => true,
            'wheelchair_accessible' => true,
            'sign_language_interpreter' => true,
            'audio_description' => true,
            'subtitles' => true,
            'tactile_paving' => true,
            'specialized_assistance' => true,
            'additional_notes' => 'Ponto de encontro Inova Acess montado ao lado do Portão C com empréstimo de kits sensoriais e fones abafadores.',
        ]);

        $event2 = Event::create([
            'organizer_id' => $org1->id,
            'name' => 'O Auto da Compadecida — Espetáculo Inclusivo',
            'description' => 'Apresentação teatral clássica com audiodescrição ao vivo para pessoas cegas e legendagem em tempo real projetada em telão lateral.',
            'location' => 'Teatro Municipal Sérgio Cardoso',
            'address' => 'Rua Rui Barbosa, 153 - Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'event_date' => now()->addDays(18)->toDateString(),
            'start_time' => '19:00:00',
            'end_time' => '21:30:00',
            'ticket_information' => 'Entrada gratuita para PCD mediante agendamento prévio.',
            'banner_url' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?w=800&h=450&fit=crop',
            'status' => 'published',
        ]);

        EventAccessibility::create([
            'event_id' => $event2->id,
            'ramps' => true,
            'accessible_restrooms' => true,
            'reserved_spaces' => true,
            'wheelchair_accessible' => true,
            'sign_language_interpreter' => true,
            'audio_description' => true,
            'subtitles' => true,
            'tactile_paving' => true,
            'specialized_assistance' => true,
            'additional_notes' => 'Recepção especial 45 minutos antes do início para apresentação dos recursos táteis de figurino.',
        ]);

        $event3 = Event::create([
            'organizer_id' => $org2->id,
            'name' => 'Expo Inovação & Tecnologia SENAI 2026',
            'description' => 'Feira nacional de projetos de robótica, inclusão e inovação industrial. Estandes com piso rebaixado, totens táteis, área calma de descompressão e intérpretes de Libras volantes.',
            'location' => 'Centro de Convenções Expo',
            'address' => 'Rua Pedro Gusso, 2635 - CIC',
            'city' => 'Curitiba',
            'state' => 'PR',
            'event_date' => now()->addDays(25)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'ticket_information' => 'Entrada Franca para todos os visitantes.',
            'banner_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=450&fit=crop',
            'status' => 'published',
        ]);

        EventAccessibility::create([
            'event_id' => $event3->id,
            'ramps' => true,
            'accessible_restrooms' => true,
            'reserved_spaces' => true,
            'wheelchair_accessible' => true,
            'sign_language_interpreter' => true,
            'audio_description' => false,
            'subtitles' => true,
            'tactile_paving' => true,
            'specialized_assistance' => true,
            'additional_notes' => 'Guias treinados para recepção e rotas de fácil circulação para cadeirantes e pessoas neurodivergentes.',
        ]);

        // ==========================================
        // 6.5. DISPONIBILIDADES DE VOLUNTÁRIOS POR EVENTO (Seção 8)
        // ==========================================
        EventVolunteer::create(['event_id' => $event1->id, 'volunteer_id' => $vol1->id, 'status' => 'available']);
        EventVolunteer::create(['event_id' => $event1->id, 'volunteer_id' => $vol2->id, 'status' => 'available']);
        EventVolunteer::create(['event_id' => $event1->id, 'volunteer_id' => $vol3->id, 'status' => 'available']);
        EventVolunteer::create(['event_id' => $event2->id, 'volunteer_id' => $vol1->id, 'status' => 'available']);
        EventVolunteer::create(['event_id' => $event2->id, 'volunteer_id' => $vol2->id, 'status' => 'available']);
        EventVolunteer::create(['event_id' => $event3->id, 'volunteer_id' => $vol4->id, 'status' => 'available']);
        EventVolunteer::create(['event_id' => $event3->id, 'volunteer_id' => $vol1->id, 'status' => 'available']);
        EventVolunteer::create(['event_id' => $event3->id, 'volunteer_id' => $vol2->id, 'status' => 'available']);

        // ==========================================
        // 7. SOLICITAÇÕES DE ACOMPANHAMENTO & CHAT
        // ==========================================
        // Solicitação 1: Aceita com Chat Ativo (Lucas & Carlos)
        $req1 = VolunteerRequest::create([
            'client_id' => $client1->id,
            'volunteer_id' => $vol2->id,
            'event_id' => $event1->id,
            'message' => 'Olá Carlos! Sou cadeirante e adoraria seu apoio para circular no gramado do festival e acessar o palco acessível.',
            'meeting_point' => 'Tenda Inova Acess ao lado do Portão C',
            'meeting_time' => '16:00:00',
            'status' => 'accepted',
            'ticket_code' => 'INOVA-TKT-A9B1-REQ1',
            'requested_at' => now()->subDays(2),
            'accepted_at' => now()->subDays(1),
        ]);

        Message::create([
            'request_id' => $req1->id,
            'sender_id' => $client1->id,
            'message' => 'Oi Carlos, tudo bem? Muito obrigado por aceitar a solicitação!',
            'created_at' => now()->subDays(1)->addMinutes(10),
            'read_at' => now()->subDays(1)->addMinutes(12),
        ]);

        Message::create([
            'request_id' => $req1->id,
            'sender_id' => $vol2->id,
            'message' => 'Olá Lucas! Vai ser um prazer enorme acompanhar você no Festival. Vamos nos encontrar na Tenda Inova Acess às 16h?',
            'created_at' => now()->subDays(1)->addMinutes(15),
            'read_at' => now()->subDays(1)->addMinutes(18),
        ]);

        Message::create([
            'request_id' => $req1->id,
            'sender_id' => $client1->id,
            'message' => 'Perfeito! Estarei com uma camiseta preta e boné azul. Até lá!',
            'created_at' => now()->subDays(1)->addMinutes(20),
            'read_at' => now()->subDays(1)->addMinutes(21),
        ]);

        // Solicitação 2: Concluída com Avaliação 5 Estrelas (Beatriz & Juliana)
        $req2 = VolunteerRequest::create([
            'client_id' => $client2->id,
            'volunteer_id' => $vol1->id,
            'event_id' => $event2->id,
            'message' => 'Olá Juliana! Preciso de acompanhamento em Libras durante a peça teatral.',
            'meeting_point' => 'Foyer de entrada do teatro',
            'meeting_time' => '18:30:00',
            'status' => 'completed',
            'ticket_code' => 'INOVA-TKT-C4F8-REQ2',
            'attendance_confirmed' => true,
            'requested_at' => now()->subDays(5),
            'accepted_at' => now()->subDays(4),
            'completed_at' => now()->subDays(3),
        ]);

        Review::create([
            'request_id' => $req2->id,
            'client_id' => $client2->id,
            'volunteer_id' => $vol1->id,
            'rating' => 5,
            'comment' => 'A Juliana foi sensacional! Muito atenciosa, pontual e com um domínio impecável de Libras. Consegui aproveitar o espetáculo do começo ao fim com total autonomia. Recomendo muito!',
            'created_at' => now()->subDays(3),
        ]);

        // Solicitação 3: Concluída com Avaliação 5 Estrelas (Fernanda & Roberto)
        $req3 = VolunteerRequest::create([
            'client_id' => $client4->id,
            'volunteer_id' => $vol4->id,
            'event_id' => $event3->id,
            'message' => 'Olá Roberto! Sou autista e busco apoio para conhecer a feira do SENAI sem ter crises sensoriais.',
            'meeting_point' => 'Estande de Credenciamento Inova',
            'meeting_time' => '10:00:00',
            'status' => 'completed',
            'ticket_code' => 'INOVA-TKT-E7D2-REQ3',
            'attendance_confirmed' => true,
            'requested_at' => now()->subDays(6),
            'accepted_at' => now()->subDays(5),
            'completed_at' => now()->subDays(4),
        ]);

        Review::create([
            'request_id' => $req3->id,
            'client_id' => $client4->id,
            'volunteer_id' => $vol4->id,
            'rating' => 5,
            'comment' => 'O Roberto foi incrível, conhecia exatamente os pontos calmos da feira e me ajudou com fones de cancelamento de ruído. Pude aproveitar todo o evento com muita tranquilidade!',
            'created_at' => now()->subDays(4),
        ]);
    }
}
