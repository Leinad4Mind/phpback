<?php
class Tag extends CI_Model {
    public function get_or_create_tags($tags) {
        $tag_ids = [];

        foreach ($tags as $tag_name) {
            $tag_name = trim(strtolower($tag_name));
            $this->db->where('name', $tag_name);
            $query = $this->db->get('tags');

            if ($query->num_rows() > 0) {
                $tag = $query->row();
                $tag_ids[] = $tag->id;
            } else {
                $this->db->insert('tags', ['name' => $tag_name]);
                $tag_ids[] = $this->db->insert_id();
            }
        }

        return $tag_ids;
    }

    public function add_tags_to_idea($idea_id, $tag_ids) {
        foreach ($tag_ids as $tag_id) {
            $this->db->insert('idea_tags', [
                'idea_id' => $idea_id,
                'tag_id' => $tag_id
            ]);
        }
    }

    public function get_tags_for_idea($idea_id) {
        $this->db->select('tags.*')
            ->from('tags')
            ->join('idea_tags', 'tags.id = idea_tags.tag_id')
            ->where('idea_tags.idea_id', $idea_id);
        return $this->db->get()->result();
    }

    public function get_all_tags() {
        return $this->db->get('tags')->result();
    }

    public function get_ideas_by_tag($tag_id) {
        $this->db->select('ideas.*')
            ->from('ideas')
            ->join('idea_tags', 'ideas.id = idea_tags.idea_id')
            ->where('idea_tags.tag_id', $tag_id);
        return $this->db->get()->result();
    }
}
